<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Business Intelligence Dashboard - Solo Gerente.
 */
final class BiController extends Controller
{
    private const MINIMO_ACEPTABLE_UNIDADES = 5;

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => static fn() => Yii::$app->user->identity
                        && Yii::$app->user->identity->isGerente(),
                ]],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('dashboard', $this->buildData(Yii::$app->request->get()));
    }

    /**
     * Calcula y guarda la rotación de inventario para TODOS los productos activos.
     * Usa stock_promedio = (stock_inicial + stock_final) / 2
     * donde stock_inicial ≈ stock_actual + vendidos_en_periodo.
     */
    /** Retorna ventas diarias de un mes dado (AJAX JSON). */
    public function actionTendenciaDiaria(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $mes  = (int)Yii::$app->request->get('mes',  (int)date('m'));
        $anio = (int)Yii::$app->request->get('anio', (int)date('Y'));

        $ini = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = date('Y-m-t', strtotime($ini));
        $dias = (int)date('t', strtotime($ini));

        $rows = (new Query())
            ->select(['dia' => 'DAY(fecha_pedido)', 'total' => 'SUM(total_efectivo)'])
            ->from('{{%venta}}')
            ->where(['estado' => 1])
            ->andWhere(['IN', 'estado_venta_id', [2, 3]])
            ->andWhere(['between', new Expression('DATE(fecha_pedido)'), $ini, $fin])
            ->groupBy([new Expression('DAY(fecha_pedido)')])
            ->indexBy('dia')
            ->all();

        $labels = [];
        $data   = [];
        for ($d = 1; $d <= $dias; $d++) {
            $labels[] = sprintf('%02d', $d);
            $data[]   = round((float)($rows[$d]['total'] ?? 0), 2);
        }

        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        return ['labels' => $labels, 'data' => $data, 'titulo' => ($meses[$mes] ?? $mes) . ' ' . $anio];
    }

    public function actionCalcularRotacion(): Response
    {
        $rotPeriodo = (string)(Yii::$app->request->get('rot_periodo', 'mes'));
        [$ini, $fin, $rotLabel, $periodoKey] = $this->resolverRangoRotacion($rotPeriodo);
        $gestion = (int)date('Y');
        $db      = Yii::$app->db;
        $userId  = Yii::$app->user->id;
        $now     = time();

        // Almacén por defecto
        $almacenId = (int)($db->createCommand('SELECT id FROM {{%almacen}} ORDER BY id LIMIT 1')->queryScalar() ?: 1);

        // Obtener TODOS los productos activos
        $productos = $db->createCommand('SELECT id, stock FROM {{%producto}} WHERE activo = 1 OR activo IS NULL')->queryAll();

        // Mapa de ventas confirmadas/entregadas en el periodo
        $ventasMap = [];
        $rows = $db->createCommand('
            SELECT d.producto_id,
                   COALESCE(SUM(d.cantidad), 0) AS total_vendido
            FROM {{%detalle_venta}} d
            INNER JOIN {{%venta}} v ON v.id = d.venta_id
            WHERE v.estado_venta_id IN (2, 3)
              AND DATE(v.fecha_pedido) BETWEEN :ini AND :fin
            GROUP BY d.producto_id
        ', [':ini' => $ini, ':fin' => $fin])->queryAll();
        foreach ($rows as $r) {
            $ventasMap[(int)$r['producto_id']] = (int)$r['total_vendido'];
        }

        $procesados = 0;
        foreach ($productos as $prod) {
            $pid         = (int)$prod['id'];
            $stockFinal  = (int)$prod['stock'];          // stock actual = stock final
            $cantVendida = $ventasMap[$pid] ?? 0;

            // stock_inicial ≈ stock_final + vendidos (lo que había antes de las ventas)
            $stockInicial = $stockFinal + $cantVendida;
            $stockProm    = ($stockInicial + $stockFinal) / 2.0;
            $rotacion     = $stockProm > 0 ? round($cantVendida / $stockProm, 2) : 0.0;

            $exists = (new Query())->from('{{%rotacion_producto}}')
                ->where(['producto_id' => $pid, 'periodo' => $periodoKey])
                ->one($db);

            if ($exists) {
                $db->createCommand()->update('{{%rotacion_producto}}', [
                    'rotacion'             => $rotacion,
                    'cantidad'             => $cantVendida,
                    'stock'                => $stockFinal,
                    'productos_entregados' => $cantVendida,
                    'gestion'              => $gestion,
                    'updated_at'           => $now,
                    'updated_by'           => $userId,
                ], ['id' => $exists['id']])->execute();
            } else {
                $db->createCommand()->insert('{{%rotacion_producto}}', [
                    'producto_id'          => $pid,
                    'cantidad'             => $cantVendida,
                    'rotacion'             => $rotacion,
                    'stock'                => $stockFinal,
                    'almacen_id'           => $almacenId,
                    'productos_entregados' => $cantVendida,
                    'gestion'              => $gestion,
                    'periodo'              => $periodoKey,
                    'created_at'           => $now,
                    'created_by'           => $userId,
                    'updated_at'           => $now,
                    'updated_by'           => $userId,
                ])->execute();
            }
            $procesados++;
        }

        Yii::$app->session->setFlash('success',
            'Rotación calculada para ' . $rotLabel . ': ' . $procesados . ' productos procesados.');
        return $this->redirect(['/bi/index', 'rot_periodo' => $rotPeriodo]);
    }

    /** Exporta el resumen del dashboard a Excel (CSV con BOM UTF-8). */
    public function actionExportarExcel(): Response
    {
        $data = $this->buildData(Yii::$app->request->get());
        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $resp->headers->set('Content-Disposition', 'attachment; filename="dashboard_bi_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Dashboard BI - Dreams Factory'], ';');
        fputcsv($out, ['Generado', date('Y-m-d H:i:s')], ';');
        fputcsv($out, ['Periodo', $data['periodoLabel']], ';');
        if (!empty($data['desde']) || !empty($data['hasta'])) {
            fputcsv($out, ['Rango', ($data['desde'] ?: '-') . ' a ' . ($data['hasta'] ?: '-')], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['INDICADORES'], ';');
        fputcsv($out, ['Ventas del periodo (Bs)',  number_format((float)$data['kpi']['ventasPeriodo'],  2, '.', '')], ';');
        fputcsv($out, ['Ventas periodo anterior (Bs)', number_format((float)$data['kpi']['ventasAnterior'], 2, '.', '')], ';');
        fputcsv($out, ['Variacion ventas (%)',  $data['kpi']['variacionVentas']], ';');
        fputcsv($out, ['Compras del periodo (Bs)', number_format((float)$data['kpi']['comprasPeriodo'], 2, '.', '')], ';');
        fputcsv($out, ['Compras periodo anterior (Bs)', number_format((float)$data['kpi']['comprasAnterior'], 2, '.', '')], ';');
        fputcsv($out, ['Variacion compras (%)', $data['kpi']['variacionCompras']], ';');
        fputcsv($out, ['Stock total (unidades)', $data['kpi']['stockTotal']], ';');
        fputcsv($out, ['Productos diferentes',   $data['kpi']['productosDiferentes']], ';');
        fputcsv($out, ['Alertas criticas',       $data['kpi']['alertasCriticas']], ';');
        fputcsv($out, []);

        fputcsv($out, ['VENTAS vs COMPRAS (ULTIMOS 6 MESES)'], ';');
        fputcsv($out, ['Mes', 'Ventas', 'Compras', 'Margen'], ';');
        foreach ($data['serieVC'] as $r) {
            fputcsv($out, [$r['label'], $r['ventas'], $r['compras'], $r['ventas'] - $r['compras']], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['DISTRIBUCION DE STOCK POR CATEGORIA'], ';');
        fputcsv($out, ['Categoria', 'Stock', '% del total'], ';');
        $totalStock = max(1, array_sum(array_column($data['stockCategoria'], 'stock')));
        foreach ($data['stockCategoria'] as $r) {
            fputcsv($out, [$r['categoria'], $r['stock'], number_format(((int)$r['stock'] * 100) / $totalStock, 2)], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['RENDIMIENTO POR VENDEDOR'], ';');
        fputcsv($out, ['Vendedor', 'Monto', 'Transacciones', 'Promedio'], ';');
        foreach ($data['vendedores'] as $r) {
            fputcsv($out, [$r['vendedor'], $r['monto'], $r['transacciones'], $r['promedio']], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['TOP 5 PRODUCTOS MAS VENDIDOS'], ';');
        fputcsv($out, ['Codigo', 'Nombre', 'Categoria', 'Cantidad', 'Monto'], ';');
        foreach ($data['topProductos'] as $r) {
            fputcsv($out, [$r['codigo'], $r['nombre'], $r['categoria'], $r['cantidad'], $r['monto']], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['TOP 5 CLIENTES POR VOLUMEN'], ';');
        fputcsv($out, ['Cliente', 'Pedidos', 'Total', '% del total'], ';');
        foreach ($data['topClientes'] as $r) {
            fputcsv($out, [$r['cliente'], $r['pedidos'], $r['total'], $r['porcentaje']], ';');
        }
        fputcsv($out, []);

        fputcsv($out, ['PRODUCTOS CON STOCK CRITICO'], ';');
        fputcsv($out, ['Codigo', 'Nombre', 'Stock actual', 'Stock minimo', 'Diferencia'], ';');
        foreach ($data['stockCritico'] as $r) {
            fputcsv($out, [$r['codigo'], $r['nombre'], $r['stock'], $r['stock_minimo'], $r['diferencia']], ';');
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $resp->sendContentAsFile($csv, 'dashboard_bi_' . date('Ymd_His') . '.csv', [
            'mimeType' => 'text/csv',
            'inline'   => false,
        ]);
    }

    /** Exporta el dashboard como HTML imprimible (PDF via window.print del navegador). */
    public function actionExportarPdf(): string
    {
        $data = $this->buildData(Yii::$app->request->get());
        $this->layout = false;
        return $this->render('pdf', $data);
    }

    // -----------------------------------------------------------------
    // Construcción de datos
    // -----------------------------------------------------------------

    /**
     * @return array{
     *   periodo:string, periodoLabel:string, desde:?string, hasta:?string,
     *   inicio:string, fin:string, inicioPrev:string, finPrev:string,
     *   kpi:array, serieVC:array, stockCategoria:array,
     *   vendedores:array, topProductos:array, topClientes:array, stockCritico:array
     * }
     */
    private function buildData(array $get): array
    {
        $periodo = (string)($get['periodo'] ?? 'mensual');
        if (!in_array($periodo, ['diario', 'semanal', 'mensual', 'anual'], true)) {
            $periodo = 'mensual';
        }
        $desde = isset($get['desde']) && $get['desde'] !== '' ? (string)$get['desde'] : null;
        $hasta = isset($get['hasta']) && $get['hasta'] !== '' ? (string)$get['hasta'] : null;

        [$inicio, $fin, $inicioPrev, $finPrev, $periodoLabel] = $this->resolverRango($periodo, $desde, $hasta);

        $kpi = [
            'ventasPeriodo'        => (float)$this->sumarVentas($inicio, $fin),
            'ventasAnterior'       => (float)$this->sumarVentas($inicioPrev, $finPrev),
            'comprasPeriodo'       => (float)$this->sumarCompras($inicio, $fin),
            'comprasAnterior'      => (float)$this->sumarCompras($inicioPrev, $finPrev),
            'stockTotal'           => (int)(new Query())->from('{{%producto}}')->sum('stock') ?: 0,
            'productosDiferentes'  => (int)(new Query())->from('{{%producto}}')->count(),
            'alertasCriticas'      => (int)(new Query())->from('{{%producto}}')
                ->where(new Expression('stock <= stock_minimo'))->count(),
        ];
        $kpi['variacionVentas']  = $this->variacion($kpi['ventasPeriodo'],  $kpi['ventasAnterior']);
        $kpi['variacionCompras'] = $this->variacion($kpi['comprasPeriodo'], $kpi['comprasAnterior']);

        // Filtros específicos de rotación
        $rotPeriodo   = (string)($get['rot_periodo'] ?? 'mes');
        $rotCatId     = (int)($get['rot_categoria_id'] ?? 0);
        $rotAlmacenId = (int)($get['rot_almacen_id'] ?? 0);
        [,, $rotLabel, $rotKey] = $this->resolverRangoRotacion($rotPeriodo);

        $data = [
            'periodo'        => $periodo,
            'periodoLabel'   => $periodoLabel,
            'desde'          => $desde,
            'hasta'          => $hasta,
            'inicio'         => $inicio,
            'fin'            => $fin,
            'inicioPrev'     => $inicioPrev,
            'finPrev'        => $finPrev,
            'kpi'            => $kpi,
            'serieVC'        => $this->serieVentasCompras6Meses(),
            'stockCategoria' => $this->stockPorCategoria(),
            'vendedores'     => $this->rendimientoVendedores($inicio, $fin),
            'topProductos'   => $this->topProductos($inicio, $fin),
            'topClientes'    => $this->topClientes($inicio, $fin, $kpi['ventasPeriodo']),
            'stockCritico'   => $this->productosCriticos(),
            'frecuenciaVendedores' => $this->frecuenciaVendedores($inicio, $fin),
            'rotacionCategoria' => $this->rotacionPorCategoria($rotKey, $rotCatId, $rotAlmacenId),
            'topRotacion'       => $this->topRotacion(10, $rotKey, $rotCatId, $rotAlmacenId),
            'bajaRotacion'      => $this->bajaRotacion($rotKey, $rotCatId, $rotAlmacenId),
            'periodoRotacion'   => $rotKey,
            'rotLabel'          => $rotLabel,
            'rotPeriodo'        => $rotPeriodo,
            'rotCategoriaId'    => $rotCatId,
            'rotAlmacenId'      => $rotAlmacenId,
            'categorias'        => $this->listaCategorias(),
            'almacenes'         => $this->listaAlmacenes(),
            'kpiVisual'         => $this->kpiVisualMesActual(),
            'rotacionVisual'    => $this->rotacionVisualMesActual(),
            'rankingVisual'     => $this->rankingVendedoresMesActual(),
            'tendenciaVisual'   => $this->tendenciaVentasSeisMeses(),
            'accionInmediata'   => $this->productosAccionInmediata(),
            'minimoAceptable'   => self::MINIMO_ACEPTABLE_UNIDADES,
        ];

        return $this->usarDatosEjemploDashboard() ? $this->aplicarDatosEjemploDashboard($data) : $data;
    }

    private function usarDatosEjemploDashboard(): bool
    {
        $demo = Yii::$app->request->get('demo', '1');
        return !in_array($demo, ['0', 0, false, 'false'], true);
    }

    private function aplicarDatosEjemploDashboard(array $data): array
    {
        $data['kpiVisual'] = [
            'ventasMes'          => 184500,
            'ventasMesPrev'      => 146200,
            'ventasVariacion'    => 26.2,
            'alertaStock'        => 7,
            'pedidosAtrasados'   => 2,
            'mejorVendedor'      => ['nombre' => 'Laura Mendoza', 'monto' => 62400],
            'periodoInicio'      => $data['kpiVisual']['periodoInicio'] ?? date('Y-m-01'),
            'periodoFin'         => $data['kpiVisual']['periodoFin'] ?? date('Y-m-t'),
            'totalComprasMes'    => 93200,
            'pedidosCompletados' => 84,
            'clientesAtendidos'  => 57,
            'productosActivos'   => max((int)($data['kpiVisual']['productosActivos'] ?? 0), 42),
            'promedioPorVenta'   => 2196,
        ];

        $data['tendenciaVisual'] = [
            'labels'    => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            'data'      => [98000, 106500, 124800, 137200, 155900, 184500],
            'meses'     => [(int)date('n', strtotime('-5 month')), (int)date('n', strtotime('-4 month')), (int)date('n', strtotime('-3 month')), (int)date('n', strtotime('-2 month')), (int)date('n', strtotime('-1 month')), (int)date('n')],
            'anios'     => [(int)date('Y', strtotime('-5 month')), (int)date('Y', strtotime('-4 month')), (int)date('Y', strtotime('-3 month')), (int)date('Y', strtotime('-2 month')), (int)date('Y', strtotime('-1 month')), (int)date('Y')],
            'direccion' => 'up',
        ];

        $data['serieVC'] = [
            ['label' => 'Ene 26', 'ventas' => 98000,  'compras' => 62000],
            ['label' => 'Feb 26', 'ventas' => 106500, 'compras' => 68000],
            ['label' => 'Mar 26', 'ventas' => 124800, 'compras' => 74500],
            ['label' => 'Abr 26', 'ventas' => 137200, 'compras' => 81000],
            ['label' => 'May 26', 'ventas' => 155900, 'compras' => 87300],
            ['label' => 'Jun 26', 'ventas' => 184500, 'compras' => 93200],
        ];

        $data['rankingVisual'] = [
            ['user_id' => 101, 'vendedor' => 'Laura Mendoza', 'monto' => 62400, 'ventas' => 28, 'variacion' => 18.4, 'color' => '#ffc107', 'medal' => '🏆'],
            ['user_id' => 102, 'vendedor' => 'Carlos Rojas', 'monto' => 51800, 'ventas' => 23, 'variacion' => 11.6, 'color' => '#6c757d', 'medal' => '🥈'],
            ['user_id' => 103, 'vendedor' => 'Mariana Flores', 'monto' => 44750, 'ventas' => 21, 'variacion' => 9.8, 'color' => '#cd7f32', 'medal' => '🥉'],
            ['user_id' => 104, 'vendedor' => 'Jorge Paredes', 'monto' => 39200, 'ventas' => 18, 'variacion' => 6.2, 'color' => '#0d6efd', 'medal' => ''],
            ['user_id' => 105, 'vendedor' => 'Sofía Vera', 'monto' => 28450, 'ventas' => 16, 'variacion' => 4.7, 'color' => '#17a2b8', 'medal' => ''],
        ];

        $data['rotacionVisual'] = [
            ['producto' => 'BG-P101 - Shampoo Herbal', 'vendido' => 48, 'rotacion' => 5.40, 'estado' => 'Alta', 'color' => '#28a745'],
            ['producto' => 'BG-P102 - Acondicionador Brillo', 'vendido' => 39, 'rotacion' => 4.10, 'estado' => 'Alta', 'color' => '#28a745'],
            ['producto' => 'BG-P103 - Crema Facial', 'vendido' => 31, 'rotacion' => 3.60, 'estado' => 'Media', 'color' => '#ffc107'],
            ['producto' => 'BG-P104 - Jabón Natural', 'vendido' => 27, 'rotacion' => 3.10, 'estado' => 'Media', 'color' => '#ffc107'],
            ['producto' => 'BG-P105 - Aceite Capilar', 'vendido' => 22, 'rotacion' => 2.70, 'estado' => 'Media', 'color' => '#ffc107'],
            ['producto' => 'BG-P106 - Mascarilla Reparadora', 'vendido' => 17, 'rotacion' => 2.10, 'estado' => 'Media', 'color' => '#ffc107'],
            ['producto' => 'BG-P107 - Serum Vitamina C', 'vendido' => 12, 'rotacion' => 1.40, 'estado' => 'Baja', 'color' => '#dc3545'],
            ['producto' => 'BG-P108 - Tónico Facial', 'vendido' => 8,  'rotacion' => 0.95, 'estado' => 'Baja', 'color' => '#dc3545'],
        ];

        return $data;
    }

    private function kpiVisualMesActual(): array
    {
        $iniMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $iniPrev = date('Y-m-01', strtotime('first day of last month'));
        $finPrev = date('Y-m-t', strtotime('first day of last month'));

        $ventasMes = (float)$this->sumarVentas($iniMes, $finMes);
        $ventasMesPrev = (float)$this->sumarVentas($iniPrev, $finPrev);
        $variacion = $this->variacion($ventasMes, $ventasMesPrev);

        $alertaStock = (int)((new Query())
            ->from('{{%producto}}')
            ->where(['activo' => 1])
            ->andWhere(new Expression('stock < stock_minimo'))
            ->count());

        $pedidosAtrasados = (int)((new Query())
            ->from('{{%venta}}')
            ->where(['estado' => 1])
            ->andWhere(['in', 'estado_venta_id', [1, 2]])
            ->andWhere(['is not', 'fecha_entrega', null])
            ->andWhere(['<', 'DATE(fecha_entrega)', date('Y-m-d')])
            ->count());

        $mejorVendedor = (new Query())
            ->select([
                'nombre' => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                'monto' => 'COALESCE(SUM(v.total_efectivo),0)',
                'ventas' => 'COUNT(*)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
            ->where(['v.estado' => 1])
            ->andWhere(['between', 'v.fecha_pedido', $iniMes, $finMes])
            ->groupBy(['v.user_id'])
            ->orderBy(['monto' => SORT_DESC])
            ->one();

        if ($mejorVendedor === false || $mejorVendedor === null) {
            $mejorVendedor = ['nombre' => 'Sin ventas', 'monto' => 0, 'ventas' => 0];
        }

        $db = Yii::$app->db;

        $totalComprasMes = (float)$db->createCommand(
            "SELECT COALESCE(SUM(importe_total),0) FROM {{%compra}} WHERE estado=1 AND DATE(fecha_compra) BETWEEN :i AND :f",
            [':i' => $iniMes, ':f' => $finMes]
        )->queryScalar();

        $pedidosCompletados = (int)$db->createCommand(
            "SELECT COUNT(*) FROM {{%venta}} WHERE estado=1 AND estado_venta_id IN (2,3) AND DATE(fecha_pedido) BETWEEN :i AND :f",
            [':i' => $iniMes, ':f' => $finMes]
        )->queryScalar();

        $clientesAtendidos = (int)$db->createCommand(
            "SELECT COUNT(DISTINCT cliente_id) FROM {{%venta}} WHERE estado=1 AND DATE(fecha_pedido) BETWEEN :i AND :f",
            [':i' => $iniMes, ':f' => $finMes]
        )->queryScalar();

        $productosActivos = (int)(new Query())->from('{{%producto}}')->where(['activo' => 1])->count();

        $totalVentas = (int)$db->createCommand(
            "SELECT COUNT(*) FROM {{%venta}} WHERE estado=1 AND DATE(fecha_pedido) BETWEEN :i AND :f",
            [':i' => $iniMes, ':f' => $finMes]
        )->queryScalar();
        $promedioPorVenta = $totalVentas > 0 ? round($ventasMes / $totalVentas, 2) : 0.0;

        return [
            'ventasMes'          => $ventasMes,
            'ventasMesPrev'      => $ventasMesPrev,
            'ventasVariacion'    => $variacion,
            'alertaStock'        => $alertaStock,
            'pedidosAtrasados'   => $pedidosAtrasados,
            'mejorVendedor'      => $mejorVendedor,
            'periodoInicio'      => $iniMes,
            'periodoFin'         => $finMes,
            'totalComprasMes'    => $totalComprasMes,
            'pedidosCompletados' => $pedidosCompletados,
            'clientesAtendidos'  => $clientesAtendidos,
            'productosActivos'   => $productosActivos,
            'promedioPorVenta'   => $promedioPorVenta,
        ];
    }

    private function rotacionVisualMesActual(): array
    {
        $iniMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $periodo = date('Y-m');

        $rows = Yii::$app->db->createCommand(
            'SELECT
                p.id,
                COALESCE(NULLIF(p.codigo, ""), CONCAT("BG-P", p.id)) AS codigo,
                p.nombre,
                COALESCE(SUM(CASE WHEN v.id IS NOT NULL THEN d.cantidad ELSE 0 END), 0) AS vendido,
                COALESCE(r.rotacion, 0) AS rotacion
            FROM {{%producto}} p
            LEFT JOIN {{%detalle_venta}} d ON d.producto_id = p.id
            LEFT JOIN {{%venta}} v
                   ON v.id = d.venta_id
                  AND v.estado = 1
                  AND DATE(v.fecha_pedido) BETWEEN :ini AND :fin
            LEFT JOIN {{%rotacion_producto}} r
                   ON r.producto_id = p.id
                  AND r.periodo = :periodo
            WHERE p.activo = 1 OR p.activo IS NULL
            GROUP BY p.id, p.codigo, p.nombre, r.rotacion',
            [':ini' => $iniMes, ':fin' => $finMes, ':periodo' => $periodo]
        )->queryAll();

        if (!$rows) {
            return [];
        }

        $top = $rows;
        usort($top, static fn(array $a, array $b): int => ((int)$b['vendido']) <=> ((int)$a['vendido']));
        $top = array_slice($top, 0, 5);

        $bottom = $rows;
        usort($bottom, static fn(array $a, array $b): int => ((int)$a['vendido']) <=> ((int)$b['vendido']));
        $bottom = array_slice($bottom, 0, 5);

        $merged = [];
        foreach (array_merge($top, $bottom) as $row) {
            $merged[(int)$row['id']] = $row;
        }

        $final = array_values($merged);
        usort($final, static fn(array $a, array $b): int => ((int)$b['vendido']) <=> ((int)$a['vendido']));

        return array_map(static function (array $row): array {
            $vendido = (int)$row['vendido'];
            if ($vendido > 25) {
                $estado = 'Alta';
                $color = '#28a745';
            } elseif ($vendido >= 15) {
                $estado = 'Media';
                $color = '#ffc107';
            } else {
                $estado = 'Baja';
                $color = '#dc3545';
            }

            return [
                'producto' => (string)$row['codigo'] . ' - ' . (string)$row['nombre'],
                'vendido' => $vendido,
                'rotacion' => round((float)$row['rotacion'], 2),
                'estado' => $estado,
                'color' => $color,
            ];
        }, array_slice($final, 0, 10));
    }

    private function rankingVendedoresMesActual(): array
    {
        $iniMes  = date('Y-m-01');
        $finMes  = date('Y-m-t');
        $iniPrev = date('Y-m-01', strtotime('first day of last month'));
        $finPrev = date('Y-m-t',  strtotime('first day of last month'));

        $buildRows = static function (string $ini, string $fin): array {
            return (new Query())
                ->select([
                    'user_id' => 'v.user_id',
                    'vendedor' => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                    'monto' => 'COALESCE(SUM(v.total_efectivo),0)',
                    'ventas' => 'COUNT(*)',
                ])
                ->from(['v' => '{{%venta}}'])
                ->leftJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
                ->where(['v.estado' => 1])
                ->andWhere(['between', 'v.fecha_pedido', $ini, $fin])
                ->groupBy(['v.user_id'])
                ->orderBy(['monto' => SORT_DESC])
                ->all();
        };

        $rows = $buildRows($iniMes, $finMes);
        $prevRows = $buildRows($iniPrev, $finPrev);

        $prevMap = [];
        foreach ($prevRows as $r) {
            $prevMap[(int)$r['user_id']] = (float)$r['monto'];
        }

        $medals = ['🏆', '🥈', '🥉'];
        $colors = ['#ffc107', '#6c757d', '#cd7f32'];

        foreach ($rows as $i => &$row) {
            $row['color']  = $colors[$i] ?? '#0d6efd';
            $row['medal']  = $medals[$i] ?? '';
            $montoActual   = (float)$row['monto'];
            $montoPrev     = $prevMap[(int)$row['user_id']] ?? 0.0;
            if ($montoPrev > 0) {
                $row['variacion'] = round((($montoActual - $montoPrev) / $montoPrev) * 100, 1);
            } else {
                $row['variacion'] = $montoActual > 0 ? 100.0 : 0.0;
            }
        }
        unset($row);

        return $rows;
    }

    private function tendenciaVentasSeisMeses(): array
    {
        $labels = [];
        $data   = [];
        $meses  = [];
        $anios  = [];
        for ($i = 5; $i >= 0; $i--) {
            $ini = date('Y-m-01', strtotime("-{$i} month"));
            $fin = date('Y-m-t', strtotime("-{$i} month"));
            $labels[] = self::mesCorto((int)date('n', strtotime($ini)));
            $data[]   = round((float)$this->sumarVentas($ini, $fin), 2);
            $meses[]  = (int)date('n', strtotime($ini));
            $anios[]  = (int)date('Y', strtotime($ini));
        }

        $direccion = 'flat';
        $len = count($data);
        if ($len >= 2) {
            if ($data[$len - 1] > $data[$len - 2]) {
                $direccion = 'up';
            } elseif ($data[$len - 1] < $data[$len - 2]) {
                $direccion = 'down';
            }
        }

        return [
            'labels'    => $labels,
            'data'      => $data,
            'meses'     => $meses,
            'anios'     => $anios,
            'direccion' => $direccion,
        ];
    }

    private function productosAccionInmediata(): array
    {
        $iniMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $periodo = date('Y-m');
        $rows = Yii::$app->db->createCommand(
            'SELECT
                p.id,
                COALESCE(NULLIF(p.codigo, ""), CONCAT("BG-P", p.id)) AS codigo,
                p.nombre,
                p.stock,
                p.stock_minimo,
                COALESCE(r.rotacion, 0) AS rotacion,
                COALESCE(SUM(CASE WHEN v.id IS NOT NULL THEN d.cantidad ELSE 0 END), 0) AS vendido_mes,
                (
                    SELECT MAX(v2.fecha_pedido)
                    FROM {{%detalle_venta}} d2
                    INNER JOIN {{%venta}} v2 ON v2.id = d2.venta_id
                    WHERE d2.producto_id = p.id
                      AND v2.estado = 1
                ) AS ultima_venta
            FROM {{%producto}} p
            LEFT JOIN {{%detalle_venta}} d ON d.producto_id = p.id
            LEFT JOIN {{%venta}} v
                   ON v.id = d.venta_id
                  AND v.estado = 1
                  AND DATE(v.fecha_pedido) BETWEEN :ini_mes AND :fin_mes
            LEFT JOIN {{%rotacion_producto}} r
                   ON r.producto_id = p.id
                  AND r.periodo = :periodo
            WHERE p.activo = 1 OR p.activo IS NULL
            GROUP BY p.id, p.codigo, p.nombre, p.stock, p.stock_minimo, r.rotacion',
            [
                ':periodo' => $periodo,
                ':ini_mes' => $iniMes,
                ':fin_mes' => $finMes,
            ]
        )->queryAll();

        $hoy = strtotime(date('Y-m-d'));
        foreach ($rows as &$row) {
            $ultima = $row['ultima_venta'] ? strtotime((string)$row['ultima_venta']) : null;
            $row['dias_sin_venta'] = $ultima ? (int)floor(($hoy - $ultima) / 86400) : 9999;

            $stock = (int)$row['stock'];
            $vendidoMes = (int)$row['vendido_mes'];
            $rot = (float)$row['rotacion'];
            if ($rot <= 0.0) {
                $stockInicial = $stock + $vendidoMes;
                $stockPromedio = ($stockInicial + $stock) / 2.0;
                $rot = $stockPromedio > 0 ? round($vendidoMes / $stockPromedio, 2) : 0.0;
            }
            $row['rotacion'] = $rot;

            if ($rot > 4) {
                $row['semaforo'] = '●';
                $row['color'] = '#28a745';
                $row['estado_rotacion'] = 'Rapido';
            } elseif ($rot >= 2) {
                $row['semaforo'] = '●';
                $row['color'] = '#ffc107';
                $row['estado_rotacion'] = 'Normal';
            } else {
                $row['semaforo'] = '●';
                $row['color'] = '#dc3545';
                $row['estado_rotacion'] = 'Lento';
            }
        }
        unset($row);

        $rows = array_values(array_filter($rows, static function (array $row): bool {
            $stockCritico = (int)$row['stock'] < (int)$row['stock_minimo'];
            $rotacionBaja = (float)$row['rotacion'] < 2.0;
            $sinVentaProlongada = (int)$row['dias_sin_venta'] >= 30;
            return $stockCritico || $rotacionBaja || $sinVentaProlongada;
        }));

        usort($rows, static function (array $a, array $b): int {
            $rotCmp = (float)$a['rotacion'] <=> (float)$b['rotacion'];
            if ($rotCmp !== 0) {
                return $rotCmp;
            }

            $diasCmp = (int)$b['dias_sin_venta'] <=> (int)$a['dias_sin_venta'];
            if ($diasCmp !== 0) {
                return $diasCmp;
            }

            return ((int)$a['stock'] - (int)$a['stock_minimo']) <=> ((int)$b['stock'] - (int)$b['stock_minimo']);
        });

        return array_slice($rows, 0, 8);
    }

    /** @return array{0:string,1:string,2:string,3:string,4:string} */
    private function resolverRango(string $periodo, ?string $desde, ?string $hasta): array
    {
        if ($desde && $hasta) {
            $ini = $desde; $fin = $hasta;
            $diff = max(1, (int)((strtotime($fin) - strtotime($ini)) / 86400) + 1);
            $iniPrev = date('Y-m-d', strtotime($ini . ' -' . $diff . ' day'));
            $finPrev = date('Y-m-d', strtotime($ini . ' -1 day'));
            return [$ini, $fin, $iniPrev, $finPrev, 'Personalizado (' . $ini . ' a ' . $fin . ')'];
        }

        $hoy = date('Y-m-d');
        switch ($periodo) {
            case 'diario':
                return [$hoy, $hoy,
                    date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')),
                    'Diario (' . $hoy . ')'];
            case 'semanal':
                $iniSemana = date('Y-m-d', strtotime('monday this week'));
                $finSemana = date('Y-m-d', strtotime('sunday this week'));
                $iniPrev   = date('Y-m-d', strtotime($iniSemana . ' -7 day'));
                $finPrev   = date('Y-m-d', strtotime($iniSemana . ' -1 day'));
                return [$iniSemana, $finSemana, $iniPrev, $finPrev, 'Semanal'];
            case 'anual':
                $y = (int)date('Y');
                return [$y . '-01-01', $y . '-12-31',
                    ($y - 1) . '-01-01', ($y - 1) . '-12-31',
                    'Anual (' . $y . ')'];
            case 'mensual':
            default:
                $ini = date('Y-m-01');
                $fin = date('Y-m-t');
                $iniPrev = date('Y-m-01', strtotime('first day of last month'));
                $finPrev = date('Y-m-t',  strtotime('first day of last month'));
                return [$ini, $fin, $iniPrev, $finPrev, 'Mensual (' . date('F Y') . ')'];
        }
    }

    private function sumarVentas(string $ini, string $fin): float
    {
        $r = (new Query())->from('{{%venta}}')
            ->where(['estado' => 1, 'estado_venta_id' => 3])
            ->andWhere(['between', 'fecha_pedido', $ini, $fin])
            ->sum('total_efectivo');
        return (float)($r ?: 0);
    }

    private function sumarCompras(string $ini, string $fin): float
    {
        $r = (new Query())->from('{{%compra}}')
            ->where(['estado' => 1])
            ->andWhere(['between', 'fecha_compra', $ini, $fin])
            ->sum('importe_total');
        return (float)($r ?: 0);
    }

    private function variacion(float $actual, float $anterior): float
    {
        if ($anterior <= 0.0) {
            return $actual > 0 ? 100.0 : 0.0;
        }
        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    /** Últimos 6 meses inclusive el actual. */
    private function serieVentasCompras6Meses(): array
    {
        $out = [];
        for ($i = 5; $i >= 0; $i--) {
            $ini = date('Y-m-01', strtotime("-{$i} month"));
            $fin = date('Y-m-t',  strtotime("-{$i} month"));
            $out[] = [
                'label'   => self::mesCorto((int)date('n', strtotime($ini))) . ' ' . date('y', strtotime($ini)),
                'ventas'  => $this->sumarVentas($ini, $fin),
                'compras' => $this->sumarCompras($ini, $fin),
            ];
        }
        return $out;
    }

    private static function mesCorto(int $m): string
    {
        return ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$m - 1] ?? '';
    }

    private function stockPorCategoria(): array
    {
        return (new Query())
            ->select([
                'categoria_id' => 'p.categoria_id',
                'categoria'    => 'c.nombre',
                'stock'        => 'COALESCE(SUM(p.stock),0)',
                'productos'    => 'COUNT(p.id)',
            ])
            ->from(['p' => '{{%producto}}'])
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->groupBy(['p.categoria_id', 'c.nombre'])
            ->orderBy(['stock' => SORT_DESC])
            ->all();
    }

    private function rendimientoVendedores(string $ini, string $fin): array
    {
        return (new Query())
            ->select([
                'user_id'       => 'v.user_id',
                'vendedor'      => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                'transacciones' => 'COUNT(*)',
                'monto'         => 'COALESCE(SUM(v.total_efectivo),0)',
                'promedio'      => 'COALESCE(AVG(v.total_efectivo),0)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
            ->where(['v.estado' => 1, 'v.estado_venta_id' => 3])
            ->andWhere(['between', 'v.fecha_pedido', $ini, $fin])
            ->groupBy(['v.user_id'])
            ->orderBy(['monto' => SORT_DESC])
            ->all();
    }

    /**
     * Frecuencia de venta por vendedor en el periodo:
     * visitas/mes, clientes únicos, promedio de productos por visita, total vendido.
     */
    private function frecuenciaVendedores(string $ini, string $fin): array
    {
        $db = Yii::$app->db;

        // Días del periodo para normalizar a "por mes"
        $dias   = max(1, (int)((strtotime($fin) - strtotime($ini)) / 86400) + 1);
        $meses  = $dias / 30.0;

        $rows = $db->createCommand('
            SELECT
                v.user_id,
                COALESCE(NULLIF(TRIM(CONCAT_WS(\' \', u.nombre, u.paterno, u.materno)),\'\'), u.username) AS vendedor,
                COUNT(DISTINCT v.id)         AS visitas,
                COUNT(DISTINCT v.cliente_id) AS clientes_unicos,
                COALESCE(SUM(d.cantidad), 0) AS total_productos,
                COALESCE(SUM(v.total_efectivo), 0) AS total_vendido
            FROM {{%venta}} v
            INNER JOIN {{%user}} u ON u.id = v.user_id
            LEFT JOIN {{%detalle_venta}} d ON d.venta_id = v.id
            WHERE v.estado_venta_id IN (1, 2, 3)
              AND DATE(v.fecha_pedido) BETWEEN :ini AND :fin
            GROUP BY v.user_id, vendedor
            ORDER BY visitas DESC
        ', [':ini' => $ini, ':fin' => $fin])->queryAll();

        foreach ($rows as &$r) {
            $visitas = max(1, (int)$r['visitas']);
            $r['visitas_mes']      = round($visitas / $meses, 1);
            $r['prod_por_visita']  = round((float)$r['total_productos'] / $visitas, 1);
        }
        return $rows;
    }

    private function topProductos(string $ini, string $fin): array
    {
        return (new Query())
            ->select([
                'producto_id' => 'd.producto_id',
                'codigo'      => 'p.codigo',
                'nombre'      => 'p.nombre',
                'categoria'   => 'c.nombre',
                'cantidad'    => 'COALESCE(SUM(d.cantidad),0)',
                'monto'       => 'COALESCE(SUM(d.importe),0)',
            ])
            ->from(['d' => '{{%detalle_venta}}'])
            ->leftJoin(['v' => '{{%venta}}'],     'v.id = d.venta_id')
            ->leftJoin(['p' => '{{%producto}}'],  'p.id = d.producto_id')
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['v.estado' => 1, 'v.estado_venta_id' => 3])
            ->andWhere(['between', 'v.fecha_pedido', $ini, $fin])
            ->groupBy(['d.producto_id', 'p.codigo', 'p.nombre', 'c.nombre'])
            ->orderBy(['cantidad' => SORT_DESC])
            ->limit(5)
            ->all();
    }

    private function topClientes(string $ini, string $fin, float $totalGlobal): array
    {
        $rows = (new Query())
            ->select([
                'cliente_id' => 'v.cliente_id',
                'cliente'    => 'COALESCE(NULLIF(cl.razon_social, ""), cl.propietario)',
                'pedidos'    => 'COUNT(*)',
                'total'      => 'COALESCE(SUM(v.total_efectivo),0)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['cl' => '{{%cliente}}'], 'cl.id = v.cliente_id')
            ->where(['v.estado' => 1, 'v.estado_venta_id' => 3])
            ->andWhere(['between', 'v.fecha_pedido', $ini, $fin])
            ->groupBy(['v.cliente_id', 'cl.razon_social', 'cl.propietario'])
            ->orderBy(['total' => SORT_DESC])
            ->limit(5)
            ->all();

        $tot = $totalGlobal > 0 ? $totalGlobal : 1.0;
        foreach ($rows as &$r) {
            $r['porcentaje'] = round(((float)$r['total'] / $tot) * 100, 2);
        }
        return $rows;
    }

    private function productosCriticos(): array
    {
        $rows = (new Query())
            ->select(['id', 'codigo', 'nombre', 'stock', 'stock_minimo'])
            ->from('{{%producto}}')
            ->where(new Expression('stock <= stock_minimo'))
            ->orderBy([new Expression('(stock - stock_minimo) ASC')])
            ->limit(20)
            ->all();
        foreach ($rows as &$r) {
            $r['diferencia'] = (int)$r['stock'] - (int)$r['stock_minimo'];
        }
        return $rows;
    }

    // ── Rotación de Inventario ────────────────────────────────────────────

    /** Resuelve rango de fechas e identificador para el periodo de rotación. */
    private function resolverRangoRotacion(string $rotPeriodo): array
    {
        switch ($rotPeriodo) {
            case '3meses':
                $ini   = date('Y-m-01', strtotime('-2 months'));
                $fin   = date('Y-m-t');
                $key   = '3M-' . date('Y-m');
                $label = 'Últimos 3 meses';
                break;
            case '6meses':
                $ini   = date('Y-m-01', strtotime('-5 months'));
                $fin   = date('Y-m-t');
                $key   = '6M-' . date('Y-m');
                $label = 'Últimos 6 meses';
                break;
            case 'año':
                $ini   = date('Y-01-01');
                $fin   = date('Y-12-31');
                $key   = 'Y-' . date('Y');
                $label = 'Año ' . date('Y');
                break;
            case 'mes':
            default:
                $ini   = date('Y-m-01');
                $fin   = date('Y-m-t');
                $key   = date('Y-m');
                $label = 'Mes actual (' . date('m/Y') . ')';
                break;
        }
        return [$ini, $fin, $label, $key];
    }

    private function listaCategorias(): array
    {
        return (new Query())->select(['id', 'nombre'])->from('{{%categoria}}')->orderBy('nombre')->all();
    }

    private function listaAlmacenes(): array
    {
        return (new Query())->select(['id', 'nombre'])->from('{{%almacen}}')->orderBy('nombre')->all();
    }

    /** Rotación promedio agrupada por categoría con filtros. */
    private function rotacionPorCategoria(string $periodoKey = '', int $catId = 0, int $almacenId = 0): array
    {
        $q = (new Query())
            ->select([
                'categoria'    => 'c.nombre',
                'rotacion_avg' => 'ROUND(AVG(r.rotacion), 2)',
                'productos'    => 'COUNT(r.id)',
            ])
            ->from(['r' => '{{%rotacion_producto}}'])
            ->leftJoin(['p' => '{{%producto}}'],  'p.id = r.producto_id')
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['r.periodo' => ($periodoKey ?: date('Y-m'))])
            ->andWhere(['IS NOT', 'r.rotacion', null]);
        if ($catId > 0)     $q->andWhere(['p.categoria_id' => $catId]);
        if ($almacenId > 0) $q->andWhere(['r.almacen_id' => $almacenId]);
        return $q->groupBy(['c.nombre'])->orderBy(['rotacion_avg' => SORT_DESC])->all();
    }

    /** Top N productos con mayor rotación con filtros. */
    private function topRotacion(int $limit = 10, string $periodoKey = '', int $catId = 0, int $almacenId = 0): array
    {
        $q = (new Query())
            ->select([
                'producto_id' => 'r.producto_id',
                'codigo'      => 'p.codigo',
                'nombre'      => 'p.nombre',
                'categoria'   => 'c.nombre',
                'rotacion'    => 'r.rotacion',
                'stock'       => 'r.stock',
                'vendidos'    => 'r.productos_entregados',
            ])
            ->from(['r' => '{{%rotacion_producto}}'])
            ->leftJoin(['p' => '{{%producto}}'],  'p.id = r.producto_id')
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['r.periodo' => ($periodoKey ?: date('Y-m'))])
            ->andWhere(['IS NOT', 'r.rotacion', null]);
        if ($catId > 0)     $q->andWhere(['p.categoria_id' => $catId]);
        if ($almacenId > 0) $q->andWhere(['r.almacen_id' => $almacenId]);
        return $q->orderBy(['r.rotacion' => SORT_DESC])->limit($limit)->all();
    }

    /** Productos con rotación menor a 1 con días sin vender y recomendación. */
    private function bajaRotacion(string $periodoKey = '', int $catId = 0, int $almacenId = 0): array
    {
        $q = (new Query())
            ->select([
                'producto_id' => 'r.producto_id',
                'codigo'      => 'p.codigo',
                'nombre'      => 'p.nombre',
                'categoria'   => 'c.nombre',
                'rotacion'    => 'r.rotacion',
                'stock'       => 'r.stock',
                'vendidos'    => 'r.productos_entregados',
                'ultima_venta'=> '(SELECT MAX(v2.fecha_pedido) FROM {{%detalle_venta}} dv2'
                                . ' INNER JOIN {{%venta}} v2 ON v2.id = dv2.venta_id'
                                . ' WHERE dv2.producto_id = r.producto_id AND v2.estado_venta_id IN (2,3))',
            ])
            ->from(['r' => '{{%rotacion_producto}}'])
            ->leftJoin(['p' => '{{%producto}}'],  'p.id = r.producto_id')
            ->leftJoin(['c' => '{{%categoria}}'], 'c.id = p.categoria_id')
            ->where(['r.periodo' => ($periodoKey ?: date('Y-m'))])
            ->andWhere(['<', 'r.rotacion', 1.0]);
        if ($catId > 0)     $q->andWhere(['p.categoria_id' => $catId]);
        if ($almacenId > 0) $q->andWhere(['r.almacen_id' => $almacenId]);
        $rows = $q->orderBy(['r.rotacion' => SORT_ASC])->all();

        $hoy = time();
        foreach ($rows as &$r) {
            $rotacion = (float)$r['rotacion'];
            $stock    = (int)$r['stock'];

            // Días sin vender
            if (!empty($r['ultima_venta'])) {
                $r['dias_sin_vender'] = (int)floor(($hoy - strtotime((string)$r['ultima_venta'])) / 86400);
            } else {
                $r['dias_sin_vender'] = null; // nunca ha vendido
            }

            $dias = $r['dias_sin_vender'];
            if ($rotacion == 0 && ($dias === null || $dias > 90)) {
                $r['recomendacion'] = 'Evaluar descontinuar el producto';
            } elseif ($stock > 50 || ($dias !== null && $dias > 60)) {
                $r['recomendacion'] = 'Evaluar reducción de stock';
            } elseif ($dias !== null && $dias > 30) {
                $r['recomendacion'] = 'Considerar crear oferta';
            } else {
                $r['recomendacion'] = 'Producto de baja demanda';
            }
        }
        return $rows;
    }
}

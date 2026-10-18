<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

final class ReporteController extends Controller
{
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

    public function actionIndex()
    {
        return $this->redirect(['personal']);
    }

    /** Submódulo 1: Ranking de vendedores por mes/año */
    public function actionPersonal(): string
    {
        $req   = Yii::$app->request;
        $mes   = (int)$req->get('mes', (int)date('m'));
        $anio  = (int)$req->get('anio', (int)date('Y'));

        $rows = (new \yii\db\Query())
            ->select([
                'user_id'      => 'v.user_id',
                'vendedor'     => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                'num_ventas'   => 'COUNT(*)',
                'monto_total'  => 'SUM(v.total_efectivo)',
                'promedio'     => 'AVG(v.total_efectivo)',
                'fecha_ultima' => 'MAX(v.fecha_pedido)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['u' => '{{%user}}'], 'u.id = v.user_id')
            ->where(['IN', 'v.estado_venta_id', [2, 3]]) // Enviadas y Entregadas = ventas efectivas
            ->andWhere(['v.estado' => 1])
            ->andWhere(new \yii\db\Expression('MONTH(v.fecha_pedido) = :m AND YEAR(v.fecha_pedido) = :y', [':m' => $mes, ':y' => $anio]))
            ->groupBy(['v.user_id'])
            ->orderBy(['num_ventas' => SORT_DESC, 'monto_total' => SORT_DESC])
            ->all();

        return $this->render('personal', [
            'rows' => $rows,
            'mes'  => $mes,
            'anio' => $anio,
        ]);
    }

    /** Submódulo 2: Ventas vs Compras por mes en un año */
    public function actionMensual(): string
    {
        $anio = (int)Yii::$app->request->get('anio', (int)date('Y'));

        $ventas = (new \yii\db\Query())
            ->select([
                'mes'         => 'MONTH(fecha_pedido)',
                'total'       => 'SUM(total_efectivo)',
                'transacciones' => 'COUNT(*)',
            ])
            ->from('{{%venta}}')
            ->where(['estado_venta_id' => 3, 'estado' => 1])
            ->andWhere(new \yii\db\Expression('YEAR(fecha_pedido) = :y', [':y' => $anio]))
            ->groupBy(['MONTH(fecha_pedido)'])
            ->indexBy('mes')
            ->all();

        $compras = (new \yii\db\Query())
            ->select([
                'mes'         => 'MONTH(fecha_compra)',
                'total'       => 'SUM(importe_total)',
                'transacciones' => 'COUNT(*)',
            ])
            ->from('{{%compra}}')
            ->where(['estado' => 1])
            ->andWhere(new \yii\db\Expression('YEAR(fecha_compra) = :y', [':y' => $anio]))
            ->groupBy(['MONTH(fecha_compra)'])
            ->indexBy('mes')
            ->all();

        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $tv = (float)($ventas[$m]['total']  ?? 0);
            $tc = (float)($compras[$m]['total'] ?? 0);
            $nv = (int)  ($ventas[$m]['transacciones']  ?? 0);
            $nc = (int)  ($compras[$m]['transacciones'] ?? 0);
            $meses[] = [
                'mes'           => $m,
                'total_ventas'  => $tv,
                'total_compras' => $tc,
                'margen'        => $tv - $tc,
                'transacciones' => $nv + $nc,
                'num_ventas'    => $nv,
                'num_compras'   => $nc,
            ];
        }

        return $this->render('mensual', [
            'meses' => $meses,
            'anio'  => $anio,
        ]);
    }

    /** Submódulo 3: Detalle de Compras-Producto */
    public function actionComprasProducto(): string
    {
        $req         = Yii::$app->request;
        $proveedorId = (int)$req->get('proveedor_id', 0);
        $categoriaId = (int)$req->get('categoria_id', 0);
        $gestion     = (int)$req->get('gestion', 0);
        $desde       = trim((string)$req->get('desde', ''));
        $hasta       = trim((string)$req->get('hasta', ''));

        $q = (new \yii\db\Query())
            ->select([
                'fecha_compra'    => 'c.fecha_compra',
                'proveedor'       => 'COALESCE(NULLIF(pr.razon_social, ""), pr.nombre)',
                'producto_codigo' => 'p.codigo',
                'producto_nombre' => 'p.nombre',
                'producto_id'     => 'p.id',
                'categoria'       => 'cat.nombre',
                'cantidad'        => 'dc.cantidad',
                'precio_unitario' => 'dc.precio_unitario',
                'importe'         => 'dc.importe',
                'gestion'         => 'dc.gestion',
            ])
            ->from(['dc' => '{{%detalle_compra}}'])
            ->innerJoin(['c'   => '{{%compra}}'],     'c.id  = dc.compra_id')
            ->innerJoin(['p'   => '{{%producto}}'],   'p.id  = dc.producto_id')
            ->innerJoin(['pr'  => '{{%proveedor}}'],  'pr.id = dc.proveedor_id')
            ->innerJoin(['cat' => '{{%categoria}}'],  'cat.id = dc.categoria_id')
            ->where(['dc.estado' => 1])
            ->orderBy(['c.fecha_compra' => SORT_DESC, 'dc.id' => SORT_DESC]);

        if ($proveedorId > 0) $q->andWhere(['dc.proveedor_id' => $proveedorId]);
        if ($categoriaId > 0) $q->andWhere(['dc.categoria_id' => $categoriaId]);
        if ($gestion > 0)     $q->andWhere(['dc.gestion'      => $gestion]);
        if ($desde !== '')    $q->andWhere(['>=', 'c.fecha_compra', $desde]);
        if ($hasta !== '')    $q->andWhere(['<=', 'c.fecha_compra', $hasta]);

        $rows = $q->all();

        $proveedores = (new \yii\db\Query())
            ->select(['id', 'nombre' => 'COALESCE(NULLIF(razon_social, ""), nombre)'])
            ->from('{{%proveedor}}')
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $categorias = (new \yii\db\Query())
            ->select(['id', 'nombre'])
            ->from('{{%categoria}}')
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $gestiones = (new \yii\db\Query())
            ->select(['gestion'])
            ->from('{{%compra}}')
            ->distinct()
            ->orderBy(['gestion' => SORT_DESC])
            ->column();

        return $this->render('compras-producto', [
            'rows'        => $rows,
            'proveedores' => $proveedores,
            'categorias'  => $categorias,
            'gestiones'   => $gestiones,
            'filtros'     => compact('proveedorId', 'categoriaId', 'gestion', 'desde', 'hasta'),
        ]);
    }

    /** Submódulo Ventas-Clientes: agrupado por cliente */
    public function actionVentasClientes(): string
    {
        $req   = Yii::$app->request;
        $desde = trim((string)$req->get('desde', ''));
        $hasta = trim((string)$req->get('hasta', ''));
        $tipoId = (int)$req->get('tipo_id', 0);

        $q = (new \yii\db\Query())
            ->select([
                'cliente_id'   => 'v.cliente_id',
                'cliente'      => 'COALESCE(NULLIF(cl.razon_social, ""), cl.propietario)',
                'tipo'         => 't.farmacia',
                'ciudad'       => 'ci.nombre',
                'num_pedidos'  => 'COUNT(*)',
                'volumen'      => 'SUM(v.total_efectivo)',
                'promedio'     => 'AVG(v.total_efectivo)',
                'ultima_fecha' => 'MAX(v.fecha_pedido)',
            ])
            ->from(['v' => '{{%venta}}'])
            ->innerJoin(['cl' => '{{%cliente}}'], 'cl.id = v.cliente_id')
            ->leftJoin(['t'  => '{{%tipo}}'],   't.id  = cl.tipo_id')
            ->leftJoin(['ci' => '{{%ciudad}}'], 'ci.id = cl.ciudad_id')
            ->where(['v.estado' => 1, 'v.estado_venta_id' => 3])
            ->groupBy(['v.cliente_id'])
            ->orderBy(['volumen' => SORT_DESC]);

        if ($desde !== '')  $q->andWhere(['>=', 'v.fecha_pedido', $desde]);
        if ($hasta !== '')  $q->andWhere(['<=', 'v.fecha_pedido', $hasta]);
        if ($tipoId > 0)    $q->andWhere(['cl.tipo_id' => $tipoId]);

        $tipos = (new \yii\db\Query())->select(['id', 'nombre' => 'farmacia'])->from('{{%tipo}}')->orderBy(['farmacia' => SORT_ASC])->all();

        return $this->render('ventas-clientes', [
            'rows'    => $q->all(),
            'tipos'   => $tipos,
            'filtros' => compact('desde', 'hasta', 'tipoId'),
        ]);
    }

    /** PDF Detalle Ventas por Cliente */
    public function actionPdfCliente(int $id): string
    {
        $req   = Yii::$app->request;
        $desde = trim((string)$req->get('desde', ''));
        $hasta = trim((string)$req->get('hasta', ''));

        $cliente = (new \yii\db\Query())
            ->select([
                'id'          => 'cl.id',
                'razon_social'=> 'COALESCE(NULLIF(cl.razon_social,""), cl.propietario)',
                'propietario' => 'cl.propietario',
                'telefono'    => 'cl.contacto',
                'ciudad'      => 'ci.nombre',
                'direccion'   => 'cl.direccion',
            ])
            ->from(['cl' => '{{%cliente}}'])
            ->leftJoin(['ci' => '{{%ciudad}}'], 'ci.id = cl.ciudad_id')
            ->where(['cl.id' => $id])
            ->one();

        if (!$cliente) throw new \yii\web\NotFoundHttpException('Cliente no encontrado.');

        $q = (new \yii\db\Query())
            ->select([
                'fecha_pedido'   => 'v.fecha_pedido',
                'fecha_entrega'  => 'v.fecha_entrega',
                'estado_venta'   => 'ev.nombre',
                'estado_pago'    => 'ep.nombre',
                'importe'        => 'v.importe_total',
                'descuento'      => 'v.descuento',
                'importe_pagado' => 'v.total_efectivo',
            ])
            ->from(['v' => '{{%venta}}'])
            ->leftJoin(['ev' => '{{%estado_venta}}'], 'ev.id = v.estado_venta_id')
            ->leftJoin(['ep' => '{{%estado_pago}}'],  'ep.id = v.estado_pago_id')
            ->where(['v.cliente_id' => $id, 'v.estado' => 1])
            ->orderBy(['v.fecha_pedido' => SORT_ASC]);

        if ($desde !== '') $q->andWhere(['>=', 'v.fecha_pedido', $desde]);
        if ($hasta !== '') $q->andWhere(['<=', 'v.fecha_pedido', $hasta]);

        $ventas = $q->all();
        $total  = array_sum(array_column($ventas, 'importe_pagado'));

        $this->layout = '@app/views/layouts/print';
        $this->view->title = 'Detalle Ventas Cliente';

        return $this->render('pdf-cliente', [
            'cliente' => $cliente,
            'ventas'  => $ventas,
            'total'   => $total,
            'desde'   => $desde,
            'hasta'   => $hasta,
        ]);
    }

    /** Submódulo Resumen Clientes: Top 10 ranking visual */
    public function actionResumenClientes(): string
    {
        $req = Yii::$app->request;
        $desde = trim((string)$req->get('desde', ''));
        $hasta = trim((string)$req->get('hasta', ''));

        $base = (new \yii\db\Query())
            ->from(['v' => '{{%venta}}'])
            ->where(['v.estado' => 1, 'v.estado_venta_id' => 3]);
        if ($desde !== '') $base->andWhere(['>=', 'v.fecha_pedido', $desde]);
        if ($hasta !== '') $base->andWhere(['<=', 'v.fecha_pedido', $hasta]);

        $totalGlobal = (float)(clone $base)->select(['t' => 'COALESCE(SUM(v.total_efectivo),0)'])->scalar() ?: 0.0;

        $top = (clone $base)
            ->select([
                'cliente_id' => 'v.cliente_id',
                'cliente'    => 'COALESCE(NULLIF(cl.razon_social, ""), cl.propietario)',
                'tipo'       => 't.farmacia',
                'ciudad'     => 'ci.nombre',
                'total'      => 'SUM(v.total_efectivo)',
                'pedidos'    => 'COUNT(*)',
            ])
            ->innerJoin(['cl' => '{{%cliente}}'], 'cl.id = v.cliente_id')
            ->leftJoin(['t'  => '{{%tipo}}'],   't.id  = cl.tipo_id')
            ->leftJoin(['ci' => '{{%ciudad}}'], 'ci.id = cl.ciudad_id')
            ->groupBy(['v.cliente_id'])
            ->orderBy(['total' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('resumen-clientes', [
            'top'         => $top,
            'totalGlobal' => $totalGlobal,
            'filtros'     => compact('desde', 'hasta'),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

final class BalanceController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['cerrar-gestion', 'confirmar-cierre'],
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity?->isGerente(),
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => static fn() => Yii::$app->user->identity
                            && Yii::$app->user->identity->isGerente(),
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['compras']);
    }

    /** Submódulo 1: Historial de Compras por gestión */
    public function actionCompras(): string
    {
        $req = Yii::$app->request;
        $gestion       = (int)$req->get('gestion', (int)date('Y'));
        $proveedorId   = (int)$req->get('proveedor_id', 0);
        $estadoPagoId  = (int)$req->get('estado_pago_id', 0);
        $desde         = trim((string)$req->get('desde', ''));
        $hasta         = trim((string)$req->get('hasta', ''));

        $q = (new \yii\db\Query())
            ->select([
                'id'             => 'c.id',
                'fecha_compra'   => 'c.fecha_compra',
                'proveedor'      => 'COALESCE(NULLIF(pr.razon_social, ""), pr.nombre)',
                'nro_documento'  => 'c.nro_documento',
                'documento'      => 'c.documento',
                'importe_total'  => 'c.importe_total',
                'estado_pago_id' => 'c.estado_pago_id',
                'estado_pago'    => 'ep.nombre',
                'usuario'        => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                'gestion'        => 'c.gestion',
            ])
            ->from(['c' => '{{%compra}}'])
            ->innerJoin(['pr' => '{{%proveedor}}'], 'pr.id = c.proveedor_id')
            ->leftJoin(['ep' => '{{%estado_pago}}'], 'ep.id = c.estado_pago_id')
            ->leftJoin(['u'  => '{{%user}}'], 'u.id = c.created_by')
            ->where(['c.estado' => 1, 'c.gestion' => $gestion])
            ->orderBy(['c.fecha_compra' => SORT_DESC, 'c.id' => SORT_DESC]);

        if ($proveedorId > 0)  $q->andWhere(['c.proveedor_id'   => $proveedorId]);
        if ($estadoPagoId > 0) $q->andWhere(['c.estado_pago_id' => $estadoPagoId]);
        if ($desde !== '')     $q->andWhere(['>=', 'c.fecha_compra', $desde]);
        if ($hasta !== '')     $q->andWhere(['<=', 'c.fecha_compra', $hasta]);

        $rows = $q->all();

        // Totales del filtro
        $totalGestion  = 0; $totalPagado = 0; $totalPendiente = 0;
        foreach ($rows as $r) {
            $totalGestion  += (float)$r['importe_total'];
            if ((int)$r['estado_pago_id'] === 2) $totalPagado    += (float)$r['importe_total'];
            else                                  $totalPendiente += (float)$r['importe_total'];
        }

        return $this->render('compras', [
            'rows'         => $rows,
            'gestiones'    => $this->gestionesCompra(),
            'proveedores'  => $this->proveedores(),
            'estadosPago'  => $this->estadosPago(),
            'cerrada'      => $this->isGestionCerrada($gestion),
            'totales'      => [
                'gestion'   => $totalGestion,
                'pagado'    => $totalPagado,
                'pendiente' => $totalPendiente,
            ],
            'filtros'      => compact('gestion', 'proveedorId', 'estadoPagoId', 'desde', 'hasta'),
        ]);
    }

    /** Submódulo 2: Historial de Ventas */
    public function actionVentas(): string
    {
        $req = Yii::$app->request;
        $gestion       = (int)$req->get('gestion', (int)date('Y'));
        $vendedorId    = (int)$req->get('user_id', 0);
        $clienteId     = (int)$req->get('cliente_id', 0);
        $estadoVentaId = (int)$req->get('estado_venta_id', 0);
        $estadoPagoId  = (int)$req->get('estado_pago_id', 0);
        $desde         = trim((string)$req->get('desde', ''));
        $hasta         = trim((string)$req->get('hasta', ''));

        $q = (new \yii\db\Query())
            ->select([
                'id'              => 'v.id',
                'codigo'          => 'v.codigo',
                'fecha_pedido'    => 'v.fecha_pedido',
                'cliente'         => 'COALESCE(NULLIF(cl.razon_social, ""), cl.propietario)',
                'vendedor'        => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)",
                'importe_total'   => 'v.importe_total',
                'descuento'       => 'v.descuento',
                'total_efectivo'  => 'v.total_efectivo',
                'estado_venta_id' => 'v.estado_venta_id',
                'estado_venta'    => 'ev.nombre',
                'estado_pago_id'  => 'v.estado_pago_id',
                'estado_pago'     => 'ep.nombre',
                'gestion'         => 'v.gestion',
            ])
            ->from(['v' => '{{%venta}}'])
            ->innerJoin(['cl' => '{{%cliente}}'], 'cl.id = v.cliente_id')
            ->leftJoin(['u'  => '{{%user}}'], 'u.id = v.user_id')
            ->leftJoin(['ev' => '{{%estado_venta}}'], 'ev.id = v.estado_venta_id')
            ->leftJoin(['ep' => '{{%estado_pago}}'], 'ep.id = v.estado_pago_id')
            ->where(['v.estado' => 1, 'v.gestion' => $gestion])
            ->orderBy(['v.fecha_pedido' => SORT_DESC, 'v.id' => SORT_DESC]);

        if ($vendedorId > 0)    $q->andWhere(['v.user_id'         => $vendedorId]);
        if ($clienteId > 0)     $q->andWhere(['v.cliente_id'      => $clienteId]);
        if ($estadoVentaId > 0) $q->andWhere(['v.estado_venta_id' => $estadoVentaId]);
        if ($estadoPagoId > 0)  $q->andWhere(['v.estado_pago_id'  => $estadoPagoId]);
        if ($desde !== '')      $q->andWhere(['>=', 'v.fecha_pedido', $desde]);
        if ($hasta !== '')      $q->andWhere(['<=', 'v.fecha_pedido', $hasta]);

        $rows = $q->all();

        $totalVendido = 0; $totalCobrado = 0; $totalPendiente = 0;
        foreach ($rows as $r) {
            $totalVendido += (float)$r['total_efectivo'];
            if ((int)$r['estado_pago_id'] === 2) $totalCobrado   += (float)$r['total_efectivo'];
            else                                  $totalPendiente += (float)$r['total_efectivo'];
        }

        return $this->render('ventas', [
            'rows'          => $rows,
            'gestiones'     => $this->gestionesVenta(),
            'vendedores'    => $this->vendedores(),
            'clientes'      => $this->clientes(),
            'estadosVenta'  => $this->estadosVenta(),
            'estadosPago'   => $this->estadosPago(),
            'cerrada'       => $this->isGestionCerrada($gestion),
            'totales'       => [
                'vendido'   => $totalVendido,
                'cobrado'   => $totalCobrado,
                'pendiente' => $totalPendiente,
            ],
            'filtros'       => compact('gestion', 'vendedorId', 'clienteId', 'estadoVentaId', 'estadoPagoId', 'desde', 'hasta'),
        ]);
    }

    /** Submódulo 3: Cerrar Gestión (solo Gerente) */
    public function actionCerrarGestion(): string
    {
        $gestion = (int)Yii::$app->request->get('gestion', (int)date('Y'));
        $resumen = $this->resumenGestion($gestion);
        $cierres = (new \yii\db\Query())
            ->select(['gc.*', 'usuario' => "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombre, u.paterno, u.materno)),''), u.username)"])
            ->from(['gc' => '{{%gestion_cierre}}'])
            ->leftJoin(['u' => '{{%user}}'], 'u.id = gc.created_by')
            ->orderBy(['gc.gestion' => SORT_DESC])
            ->all();

        return $this->render('cerrar-gestion', [
            'gestion'   => $gestion,
            'resumen'   => $resumen,
            'cerrada'   => $this->isGestionCerrada($gestion),
            'gestiones' => $this->gestionesGlobales(),
            'cierres'   => $cierres,
        ]);
    }

    public function actionConfirmarCierre()
    {
        if (!Yii::$app->request->isPost) {
            throw new ForbiddenHttpException('Método no permitido.');
        }
        $gestion       = (int)Yii::$app->request->post('gestion');
        $observaciones = trim((string)Yii::$app->request->post('observaciones', ''));

        if ($gestion <= 0) {
            Yii::$app->session->setFlash('danger', 'Gestión inválida.');
            return $this->redirect(['cerrar-gestion']);
        }
        if ($this->isGestionCerrada($gestion)) {
            Yii::$app->session->setFlash('warning', "La gestión {$gestion} ya estaba cerrada.");
            return $this->redirect(['cerrar-gestion', 'gestion' => $gestion]);
        }

        $r = $this->resumenGestion($gestion);

        Yii::$app->db->createCommand()->insert('{{%gestion_cierre}}', [
            'gestion'         => $gestion,
            'fecha_cierre'    => date('Y-m-d H:i:s'),
            'total_compras'   => $r['total_compras'],
            'total_ventas'    => $r['total_ventas'],
            'total_descuentos'=> $r['total_descuentos'],
            'total_cobrado'   => $r['total_cobrado'],
            'total_pagado'    => $r['total_pagado'],
            'num_compras'     => $r['num_compras'],
            'num_ventas'      => $r['num_ventas'],
            'margen'          => $r['margen'],
            'observaciones'   => $observaciones !== '' ? $observaciones : null,
            'created_by'      => Yii::$app->user->id,
            'created_at'      => time(),
        ])->execute();

        Yii::$app->session->setFlash('success', "Gestión {$gestion} cerrada exitosamente. Compras y ventas de ese año quedan bloqueadas.");
        return $this->redirect(['cerrar-gestion', 'gestion' => $gestion]);
    }

    // ---------- helpers ----------

    public static function isGestionCerrada(int $gestion): bool
    {
        return (new \yii\db\Query())
            ->from('{{%gestion_cierre}}')
            ->where(['gestion' => $gestion])
            ->exists();
    }

    private function resumenGestion(int $gestion): array
    {
        $compras = (new \yii\db\Query())
            ->select([
                'total' => 'COALESCE(SUM(importe_total), 0)',
                'num'   => 'COUNT(*)',
                'pagado'=> 'COALESCE(SUM(CASE WHEN estado_pago_id = 2 THEN importe_total ELSE 0 END), 0)',
            ])
            ->from('{{%compra}}')
            ->where(['gestion' => $gestion, 'estado' => 1])
            ->one();

        $ventas = (new \yii\db\Query())
            ->select([
                'total'      => 'COALESCE(SUM(total_efectivo), 0)',
                'num'        => 'COUNT(*)',
                'descuento'  => 'COALESCE(SUM(descuento), 0)',
                'cobrado'    => 'COALESCE(SUM(CASE WHEN estado_pago_id = 2 THEN total_efectivo ELSE 0 END), 0)',
            ])
            ->from('{{%venta}}')
            ->where(['gestion' => $gestion, 'estado' => 1])
            ->one();

        $totalC = (float)($compras['total'] ?? 0);
        $totalV = (float)($ventas['total']  ?? 0);

        return [
            'gestion'         => $gestion,
            'total_compras'   => $totalC,
            'total_ventas'    => $totalV,
            'total_descuentos'=> (float)($ventas['descuento'] ?? 0),
            'total_pagado'    => (float)($compras['pagado'] ?? 0),
            'total_cobrado'   => (float)($ventas['cobrado']  ?? 0),
            'num_compras'     => (int)($compras['num'] ?? 0),
            'num_ventas'      => (int)($ventas['num']  ?? 0),
            'margen'          => $totalV - $totalC,
        ];
    }

    private function gestionesCompra(): array
    {
        return (new \yii\db\Query())->select(['gestion'])->from('{{%compra}}')->distinct()->orderBy(['gestion' => SORT_DESC])->column();
    }

    private function gestionesVenta(): array
    {
        return (new \yii\db\Query())->select(['gestion'])->from('{{%venta}}')->distinct()->orderBy(['gestion' => SORT_DESC])->column();
    }

    private function gestionesGlobales(): array
    {
        $g = array_unique(array_merge($this->gestionesCompra(), $this->gestionesVenta(), [(int)date('Y')]));
        rsort($g);
        return array_values(array_filter($g, fn($x) => $x > 0));
    }

    private function proveedores(): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'nombre' => 'COALESCE(NULLIF(razon_social, ""), nombre)'])
            ->from('{{%proveedor}}')
            ->orderBy(['nombre' => SORT_ASC])
            ->all();
    }

    private function clientes(): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'nombre' => 'COALESCE(NULLIF(razon_social, ""), propietario)'])
            ->from('{{%cliente}}')
            ->orderBy(['razon_social' => SORT_ASC])
            ->all();
    }

    private function vendedores(): array
    {
        return (new \yii\db\Query())
            ->select(['id', 'label' => new Expression("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', nombre, paterno, materno)),''), username)")])
            ->from('{{%user}}')
            ->where(['status_id' => 1])
            ->orderBy(['username' => SORT_ASC])
            ->all();
    }

    private function estadosPago(): array
    {
        return (new \yii\db\Query())->select(['id', 'nombre'])->from('{{%estado_pago}}')->orderBy(['id' => SORT_ASC])->all();
    }

    private function estadosVenta(): array
    {
        return (new \yii\db\Query())->select(['id', 'nombre'])->from('{{%estado_venta}}')->orderBy(['id' => SORT_ASC])->all();
    }
}

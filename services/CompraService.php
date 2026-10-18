<?php

declare(strict_types=1);

namespace app\services;

use app\models\Compra;
use app\models\DetalleCompra;
use app\models\Lote;
use app\models\Producto;
use Yii;
use yii\db\Expression;

final class CompraService
{
    /**
     * Confirma la compra: guarda detalles, suma importes, actualiza stock y crea lotes LIFO.
     *
     * @param Compra $compra
     * @param DetalleCompra[] $detalles
     */
    public static function guardarConfirmada(Compra $compra, array $detalles): bool
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $total = 0.0;
            foreach ($detalles as $detalle) {
                $total += (float)$detalle->importe;
            }

            $compra->importe_total = $total;
            $compra->estado = Compra::ESTADO_CONFIRMADA;

            if (!$compra->save()) {
                $transaction->rollBack();
                return false;
            }

            foreach ($detalles as $detalle) {
                $detalle->compra_id = (int)$compra->id;
                $detalle->proveedor_id = (int)$compra->proveedor_id;
                $detalle->gestion = (int)$compra->gestion;

                if (!$detalle->save()) {
                    $transaction->rollBack();
                    return false;
                }

                $producto = Producto::findOne(['id' => (int)$detalle->producto_id]);
                if ($producto === null) {
                    $transaction->rollBack();
                    return false;
                }

                $producto->stock = (int)$producto->stock + (int)$detalle->cantidad;
                $producto->precio_compra = (float)$detalle->precio_unitario;
                if (!$producto->save(false)) {
                    $transaction->rollBack();
                    return false;
                }

                $lote = new Lote([
                    'producto_id' => (int)$detalle->producto_id,
                    'compra_id' => (int)$compra->id,
                    'detalle_compra_id' => (int)$detalle->id,
                    'almacen_id' => (int)$detalle->almacen_id,
                    'fecha_ingreso' => new Expression('NOW()'),
                    'fecha_vencimiento' => $detalle->fecha_vencimiento,
                    'cantidad_inicial' => (int)$detalle->cantidad,
                    'cantidad_disponible' => (int)$detalle->cantidad,
                    'precio_compra_unitario' => (float)$detalle->precio_unitario,
                    'estado' => 1,
                    'gestion' => (int)$compra->gestion,
                    'created_by' => (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest)
                        ? (int)Yii::$app->user->id
                        : null,
                    'created_at' => time(),
                ]);

                if (!$lote->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}

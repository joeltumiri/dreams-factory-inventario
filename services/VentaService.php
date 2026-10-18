<?php

declare(strict_types=1);

namespace app\services;

use app\models\DetalleVenta;
use app\models\Lote;
use app\models\MovimientoFifo;
use app\models\Producto;
use app\models\Venta;
use Yii;

/**
 * VentaService — guarda una venta completa en una transacción:
 *  1. Genera el código VTA-YYYY-NNN
 *  2. Persiste la cabecera (venta)
 *  3. Persiste cada detalle (detalle_venta)
 *  4. Aplica LIFO por cada producto (descuenta lotes de más reciente a más antiguo)
 *  5. Reduce el stock del producto
 */
final class VentaService
{
    /**
     * @param Venta          $venta    Cabecera ya hidratada (sin código ni totales)
     * @param DetalleVenta[] $detalles Array de líneas ya hidratadas (sin venta_id)
     * @return bool True si todo se guardó correctamente
     */
    public static function guardarConfirmada(Venta $venta, array $detalles): bool
    {
        $db          = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            $gestion       = (int)date('Y');
            $userId        = (Yii::$app->has('user') && !Yii::$app->user->isGuest)
                ? (int)Yii::$app->user->id : 1;

            $venta->codigo    = Venta::generarCodigo($gestion);
            $venta->gestion   = $gestion;
            $venta->user_id   = $userId;
            $venta->estado    = 1;

            if (!$venta->save()) {
                $transaction->rollBack();
                return false;
            }

            foreach ($detalles as $detalle) {
                $detalle->venta_id        = (int)$venta->id;
                $detalle->cliente_id      = (int)$venta->cliente_id;
                $detalle->estado_venta_id = (int)$venta->estado_venta_id;
                $detalle->tipo_pago_id    = (int)$venta->tipo_pago_id;
                $detalle->estado_pago_id  = (int)$venta->estado_pago_id;
                $detalle->gestion         = $gestion;
                $detalle->user_id         = $userId;
                $detalle->estado          = 1;

                // Guardar texto del producto para recibo
                $prod = Producto::findOne((int)$detalle->producto_id);
                if ($prod === null) {
                    $transaction->rollBack();
                    return false;
                }
                $detalle->producto_recibo_venta = ($prod->codigo ? $prod->codigo . ' | ' : '') . $prod->nombre;

                if (!$detalle->save()) {
                    $transaction->rollBack();
                    return false;
                }

                // Aplicar LIFO
                self::aplicarFifo($detalle, $prod, $userId);
            }

            $transaction->commit();
            return true;

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Descuenta $detalle->cantidad del producto usando LIFO (lote más reciente primero).
     * Actualiza cantidad_disponible de cada lote y stock del producto.
     *
     * @throws \RuntimeException si no hay stock suficiente
     */
    private static function aplicarFifo(DetalleVenta $detalle, Producto $prod, int $userId): void
    {
        $pendiente   = (int)$detalle->cantidad;
        $productoId  = (int)$prod->id;
        $fechaSalida = date('Y-m-d H:i:s');

        // LIFO: lotes activos más recientes primero
        $lotes = Lote::find()
            ->where(['producto_id' => $productoId, 'estado' => 1])
            ->andWhere(['>', 'cantidad_disponible', 0])
            ->orderBy(['fecha_ingreso' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        foreach ($lotes as $lote) {
            if ($pendiente <= 0) {
                break;
            }

            $descontar = min($pendiente, (int)$lote->cantidad_disponible);

            $mov = new MovimientoFifo();
            $mov->lote_id               = (int)$lote->id;
            $mov->detalle_venta_id      = (int)$detalle->id;
            $mov->cantidad_salida       = $descontar;
            $mov->precio_compra_unitario = (float)$lote->precio_compra_unitario;
            $mov->fecha_salida          = $fechaSalida;
            $mov->created_by            = $userId;
            $mov->save(false);

            $lote->cantidad_disponible = (int)$lote->cantidad_disponible - $descontar;
            if ($lote->cantidad_disponible <= 0) {
                $lote->estado = 0;
            }
            $lote->save(false);

            $pendiente -= $descontar;
        }

        if ($pendiente > 0) {
            throw new \RuntimeException(
                "Stock insuficiente para «{$prod->nombre}». Stock disponible: " . ((int)$prod->stock) . ", solicitado: " . ((int)$detalle->cantidad) . "."
            );
        }

        // Actualizar stock del producto
        $prod->stock = max(0, (int)$prod->stock - (int)$detalle->cantidad);
        $prod->save(false);
    }
}

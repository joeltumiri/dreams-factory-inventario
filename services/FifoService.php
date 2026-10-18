<?php

declare(strict_types=1);

namespace app\services;

use app\models\DetalleVenta;
use app\models\Lote;
use app\models\MovimientoFifo;
use app\models\Producto;
use Yii;
use yii\db\Exception;

/**
 * FifoService — implementa el método LIFO (Last In First Out) para descuento de inventario.
 *
 * Al vender: el lote más reciente (fecha_ingreso DESC) se descuenta primero,
 * para forzar la rotación del inventario y evitar acumulación de stock antiguo
 * en accesorios para bebés (sin fecha de vencimiento).
 *
 * Nota: el nombre de la clase y la tabla movimiento_fifo se mantienen por
 * compatibilidad con el resto del código y la base de datos.
 *
 * Uso:
 *   FifoService::descontarStock($detalleVenta);
 */
final class FifoService
{
    /**
     * Descuenta el stock de los lotes aplicando LIFO (más reciente primero) para un DetalleVenta dado.
     * Actualiza cantidad_disponible de cada lote, crea registros en movimiento_fifo
     * y reduce el stock del producto.
     *
     * @throws Exception si no hay stock suficiente
     */
    public static function descontarStock(DetalleVenta $detalle): void
    {
        $pendiente  = (int)$detalle->cantidad;
        $productoId = (int)$detalle->producto_id;
        $fechaSalida = date('Y-m-d H:i:s');
        $createdBy  = (Yii::$app->has('user') && !Yii::$app->user->isGuest)
            ? (int)Yii::$app->user->id
            : null;

        // LIFO: lotes activos ordenados por fecha_ingreso DESC (más reciente primero)
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

            // Registrar movimiento de salida con los nombres correctos de la tabla
            $mov = new MovimientoFifo();
            $mov->lote_id              = (int)$lote->id;
            $mov->detalle_venta_id     = (int)$detalle->id;
            $mov->cantidad_salida      = $descontar;
            $mov->precio_compra_unitario = (float)$lote->precio_compra_unitario;
            $mov->fecha_salida         = $fechaSalida;
            $mov->created_by           = $createdBy;
            $mov->save(false);

            // Actualizar lote
            $lote->cantidad_disponible = (int)$lote->cantidad_disponible - $descontar;
            if ($lote->cantidad_disponible <= 0) {
                $lote->estado = 0; // agotado
            }
            $lote->save(false);

            $pendiente -= $descontar;
        }

        if ($pendiente > 0) {
            throw new Exception(
                "Stock insuficiente: faltan {$pendiente} unidades del producto ID {$productoId}."
            );
        }

        // Actualizar stock del producto (campo: stock)
        $producto = Producto::findOne($productoId);
        if ($producto !== null) {
            $producto->stock = max(0, (int)$producto->stock - (int)$detalle->cantidad);
            $producto->save(false);
        }
    }
}

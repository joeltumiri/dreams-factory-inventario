<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Tabla movimiento_fifo — registra cada descuento de stock sobre un lote.
 *
 * @property int      $id
 * @property int      $lote_id
 * @property int      $detalle_venta_id
 * @property int      $cantidad_salida
 * @property float    $precio_compra_unitario
 * @property string   $fecha_salida
 * @property int|null $created_by
 *
 * @property-read Lote       $lote
 * @property-read DetalleVenta $detalleVenta
 */
final class MovimientoFifo extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%movimiento_fifo}}';
    }

    public function rules(): array
    {
        return [
            [['lote_id', 'detalle_venta_id', 'cantidad_salida', 'precio_compra_unitario', 'fecha_salida'], 'required'],
            [['lote_id', 'detalle_venta_id', 'cantidad_salida', 'created_by'], 'integer'],
            [['precio_compra_unitario'], 'number', 'min' => 0],
            [['fecha_salida'], 'safe'],
        ];
    }

    public function getLote(): ActiveQuery
    {
        return $this->hasOne(Lote::class, ['id' => 'lote_id']);
    }

    public function getDetalleVenta(): ActiveQuery
    {
        return $this->hasOne(DetalleVenta::class, ['id' => 'detalle_venta_id']);
    }
}

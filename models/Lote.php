<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $producto_id
 * @property int $compra_id
 * @property int $detalle_compra_id
 * @property int $almacen_id
 * @property string $fecha_ingreso
 * @property string|null $fecha_vencimiento
 * @property int $cantidad_inicial
 * @property int $cantidad_disponible
 * @property float $precio_compra_unitario
 * @property int $estado
 * @property int $gestion
 */
final class Lote extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%lote}}';
    }

    public function rules(): array
    {
        return [
            [['producto_id', 'almacen_id', 'fecha_ingreso', 'cantidad_inicial', 'cantidad_disponible', 'precio_compra_unitario', 'gestion'], 'required'],
            [['producto_id', 'compra_id', 'detalle_compra_id', 'almacen_id', 'cantidad_inicial', 'cantidad_disponible', 'estado', 'gestion'], 'integer'],
            [['precio_compra_unitario'], 'number'],
            [['fecha_ingreso', 'fecha_vencimiento'], 'safe'],
        ];
    }

    public function getProducto(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }

    public function getAlmacen(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Almacen::class, ['id' => 'almacen_id']);
    }

    public function getCompra(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Compra::class, ['id' => 'compra_id']);
    }
}

<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $producto_id
 * @property int $cantidad
 * @property float $precio_unitario
 * @property float $importe
 * @property int $categoria_id
 * @property string|null $fecha_vencimiento
 * @property string|null $fecha_elaboracion
 * @property int $compra_id
 * @property int $proveedor_id
 * @property int $almacen_id
 * @property int $gestion
 * @property int $estado
 *
 * @property-read Producto $producto
 * @property-read Compra $compra
 */
final class DetalleCompra extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%detalle_compra}}';
    }

    public function rules(): array
    {
        return [
            [['producto_id', 'cantidad', 'precio_unitario', 'importe', 'categoria_id', 'compra_id', 'proveedor_id', 'almacen_id', 'gestion'], 'required'],
            [['producto_id', 'cantidad', 'categoria_id', 'compra_id', 'proveedor_id', 'almacen_id', 'gestion', 'estado'], 'integer'],
            [['precio_unitario', 'importe'], 'number', 'min' => 0],
            [['fecha_vencimiento', 'fecha_elaboracion'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'producto_id' => 'Producto',
            'cantidad' => 'Cantidad',
            'precio_unitario' => 'Precio unitario',
            'importe' => 'Importe',
            'fecha_elaboracion' => 'Fecha elaboración',
            'fecha_vencimiento' => 'Fecha vencimiento',
        ];
    }

    public function getProducto(): ActiveQuery
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }

    public function getCompra(): ActiveQuery
    {
        return $this->hasOne(Compra::class, ['id' => 'compra_id']);
    }
}

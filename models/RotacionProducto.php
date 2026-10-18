<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int         $id
 * @property int         $producto_id
 * @property int|null    $cantidad
 * @property float|null  $rotacion
 * @property int|null    $stock
 * @property int         $almacen_id
 * @property int|null    $productos_entregados
 * @property int|null    $productos_confirmados
 * @property int|null    $productos_pendientes
 * @property int|null    $productos_enviados
 * @property int|null    $gestion
 * @property string|null $periodo
 * @property int|null    $total_fisico
 * @property int|null    $diferencia
 * @property int|null    $created_at
 * @property int|null    $created_by
 * @property int|null    $updated_at
 * @property int|null    $updated_by
 *
 * @property-read Producto $producto
 * @property-read Almacen  $almacen
 */
class RotacionProducto extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%rotacion_producto}}';
    }

    public function rules(): array
    {
        return [
            [['producto_id', 'almacen_id'], 'required'],
            [['producto_id', 'cantidad', 'stock', 'almacen_id', 'productos_entregados',
              'productos_confirmados', 'productos_pendientes', 'productos_enviados',
              'gestion', 'total_fisico', 'diferencia',
              'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['rotacion'], 'number'],
            [['periodo'], 'string', 'max' => 7],
        ];
    }

    public function getProducto(): ActiveQuery
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }

    public function getAlmacen(): ActiveQuery
    {
        return $this->hasOne(Almacen::class, ['id' => 'almacen_id']);
    }
}

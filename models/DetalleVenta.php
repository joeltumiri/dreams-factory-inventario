<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Tabla detalle_venta — productos de una venta.
 *
 * @property int         $id
 * @property int         $producto_id
 * @property string|null $producto_recibo_venta
 * @property float       $precio_unitario
 * @property float|null  $precio_real
 * @property int         $cantidad
 * @property float       $importe
 * @property int         $venta_id
 * @property int         $estado_venta_id
 * @property int         $tipo_pago_id
 * @property int         $cliente_id
 * @property int         $gestion
 * @property int         $estado
 * @property int         $user_id
 * @property int         $estado_pago_id
 * @property string|null $descripcion
 * @property int|null    $precio_venta_id
 * @property int|null    $created_by
 * @property int|null    $created_at
 * @property int|null    $updated_by
 * @property int|null    $updated_at
 *
 * @property-read Producto $producto
 * @property-read Venta    $venta
 */
final class DetalleVenta extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%detalle_venta}}';
    }

    public function rules(): array
    {
        return [
            [['producto_id', 'precio_unitario', 'cantidad', 'importe', 'venta_id',
              'estado_venta_id', 'tipo_pago_id', 'cliente_id', 'gestion', 'user_id', 'estado_pago_id'], 'required'],
            [['producto_id', 'cantidad', 'venta_id', 'estado_venta_id', 'tipo_pago_id',
              'cliente_id', 'gestion', 'estado', 'user_id', 'estado_pago_id',
              'precio_venta_id', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['precio_unitario', 'precio_real', 'importe'], 'number', 'min' => 0],
            [['producto_recibo_venta', 'descripcion'], 'string', 'max' => 255],
            [['estado'], 'default', 'value' => 1],
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
            'descripcion' => 'Descripción',
            'venta_id' => 'Venta',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = time();
        $userId = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int)Yii::$app->user->id : null;
        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getProducto(): ActiveQuery
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }

    public function getVenta(): ActiveQuery
    {
        return $this->hasOne(Venta::class, ['id' => 'venta_id']);
    }
}

<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int        $id
 * @property float      $precio
 * @property string|null $obs
 * @property int        $producto_id
 * @property int        $ciudad_id
 * @property int        $departamento_id
 * @property int        $estado
 *
 * @property-read Producto $producto
 * @property-read Ciudad   $ciudad
 */
final class PrecioVenta extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%precio_venta}}';
    }

    public function rules(): array
    {
        return [
            [['producto_id', 'ciudad_id', 'precio'], 'required'],
            [['producto_id', 'ciudad_id', 'departamento_id', 'estado'], 'integer'],
            [['precio', 'precio_original', 'precio_oferta'], 'number', 'min' => 0],
            [['obs'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'producto_id' => 'Producto',
            'ciudad_id' => 'Ciudad',
            'departamento_id' => 'Departamento',
            'precio' => 'Precio de Venta',
            'obs' => 'Observación',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = time();
        $userId = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int)Yii::$app->user->id : null;

        // Inferir departamento desde ciudad
        if ($this->ciudad_id && empty($this->departamento_id)) {
            $depId = (int)Yii::$app->db->createCommand(
                "SELECT departamento_id FROM {{%ciudad}} WHERE id = :id"
            )->bindValue(':id', $this->ciudad_id)->queryScalar();
            if ($depId > 0) $this->departamento_id = $depId;
        }

        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
            if ($this->estado === null) $this->estado = 1;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getProducto(): ActiveQuery
    {
        return $this->hasOne(Producto::class, ['id' => 'producto_id']);
    }

    public function getCiudad(): ActiveQuery
    {
        return $this->hasOne(Ciudad::class, ['id' => 'ciudad_id']);
    }
}

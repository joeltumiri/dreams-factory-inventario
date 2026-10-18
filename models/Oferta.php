<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $fecha_inicio
 * @property string $fecha_final
 * @property int    $producto_id
 * @property float  $precio_venta
 * @property float  $precio_oferta
 * @property string|null $obs
 * @property int    $ciudad_id
 * @property int    $departamento_id
 * @property int|null $precio_venta_id
 *
 * @property-read Producto $producto
 * @property-read Ciudad   $ciudad
 */
final class Oferta extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%oferta}}';
    }

    public function rules(): array
    {
        return [
            [['fecha_inicio', 'fecha_final', 'producto_id', 'ciudad_id', 'precio_oferta'], 'required'],
            [['producto_id', 'ciudad_id', 'departamento_id', 'precio_venta_id'], 'integer'],
            [['fecha_inicio', 'fecha_final'], 'date', 'format' => 'php:Y-m-d'],
            [['precio_venta', 'precio_oferta'], 'filter', 'filter' => fn($v) => (float) str_replace(',', '.', (string)$v)],
            [['precio_venta', 'precio_oferta'], 'number', 'min' => 0],
            [['precio_oferta'], 'compare', 'compareAttribute' => 'precio_venta', 'operator' => '<=', 'message' => 'El precio oferta debe ser menor o igual al precio original.'],
            [['fecha_final'], 'compare', 'compareAttribute' => 'fecha_inicio', 'operator' => '>=', 'message' => 'La fecha fin debe ser igual o posterior a la fecha de inicio.'],
            [['obs'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'fecha_inicio' => 'Fecha de inicio',
            'fecha_final' => 'Fecha de fin',
            'producto_id' => 'Producto',
            'ciudad_id' => 'Ciudad',
            'departamento_id' => 'Departamento',
            'precio_venta' => 'Precio Original',
            'precio_oferta' => 'Precio Oferta',
            'obs' => 'Observaciones',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = time();
        $userId = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int)Yii::$app->user->id : null;

        if ($this->ciudad_id && empty($this->departamento_id)) {
            $depId = (int)Yii::$app->db->createCommand(
                "SELECT departamento_id FROM {{%ciudad}} WHERE id = :id"
            )->bindValue(':id', $this->ciudad_id)->queryScalar();
            if ($depId > 0) $this->departamento_id = $depId;
        }

        if ($insert) {
            $this->created_at = $now;
            $this->created_by = $userId;
        }
        $this->updated_at = $now;
        $this->updated_by = $userId;
        return true;
    }

    public function getAhorro(): float
    {
        return max(0.0, (float)$this->precio_venta - (float)$this->precio_oferta);
    }

    public function isActiva(?string $hoy = null): bool
    {
        $hoy = $hoy ?: date('Y-m-d');
        return $this->fecha_inicio <= $hoy && $hoy <= $this->fecha_final;
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

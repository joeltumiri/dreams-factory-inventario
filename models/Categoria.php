<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $nota
 * @property int $activo
 */
final class Categoria extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%categoria}}';
    }

    public function rules(): array
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 120],
            [['nota'], 'string', 'max' => 255],
            [['activo'], 'integer'],
            [['activo'], 'default', 'value' => 1],
            [['nombre'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'nota' => 'Descripción',
            'activo' => 'Estado',
        ];
    }

    public function getProductos(): ActiveQuery
    {
        return $this->hasMany(Producto::class, ['categoria_id' => 'id']);
    }

    /** Cantidad de productos asociados a esta categoría. */
    public function getProductosCount(): int
    {
        return (int)$this->getProductos()->count();
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();

        if ($insert && ($this->created_at === null || $this->created_at === 0)) {
            $this->created_at = $now;
            $this->created_by = (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest)
                ? (int)Yii::$app->user->id
                : ($this->created_by ?? null);
        }

        $this->updated_at = $now;
        $this->updated_by = (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest)
            ? (int)Yii::$app->user->id
            : ($this->updated_by ?? null);

        return true;
    }
}

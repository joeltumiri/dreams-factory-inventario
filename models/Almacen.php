<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Tabla almacen.
 *
 * @property int         $id
 * @property string      $nombre
 * @property string|null $direccion
 * @property string|null $contacto
 * @property int|null    $created_by
 * @property int|null    $created_at
 * @property int|null    $updated_by
 * @property int|null    $updated_at
 */
final class Almacen extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%almacen}}';
    }

    public function rules(): array
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 120],
            [['nombre'], 'unique', 'message' => 'Ya existe un almacén con ese nombre.'],
            [['direccion', 'contacto'], 'string', 'max' => 255],
            [['created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'        => 'ID',
            'nombre'    => 'Nombre del Almacén',
            'direccion' => 'Dirección',
            'contacto'  => 'Fono / Cel.',
            'created_at'=> 'Fecha Creación',
            'updated_at'=> 'Fecha Actualización',
        ];
    }

    public function getCreatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = time();
        if ($insert) {
            $this->created_by = Yii::$app->user->id ?? null;
            $this->created_at = $now;
        }
        $this->updated_by = Yii::$app->user->id ?? null;
        $this->updated_at = $now;
        return true;
    }
}

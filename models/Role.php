<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $role_name
 * @property int $role_value
 */
final class Role extends ActiveRecord
{
    public const GERENTE = 1;
    public const ALMACEN = 2;
    public const VENDEDOR = 3;

    public static function tableName(): string
    {
        return '{{%role}}';
    }

    public function rules(): array
    {
        return [
            [['role_name', 'role_value'], 'required'],
            [['role_value'], 'integer'],
            [['role_name'], 'string', 'max' => 80],
            [['role_value'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'role_name' => 'Rol',
            'role_value' => 'Valor',
        ];
    }
}

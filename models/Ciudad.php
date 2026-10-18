<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property string $nombre
 * @property int|null $departamento_id
 *
 * @property Departamento|null $departamento
 */
final class Ciudad extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ciudad}}';
    }

    public function getDepartamento(): ActiveQuery
    {
        return $this->hasOne(Departamento::class, ['id' => 'departamento_id']);
    }
}

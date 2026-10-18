<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $nombre
 * @property string|null $sigla
 */
final class Departamento extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%departamento}}';
    }
}

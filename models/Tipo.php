<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $farmacia
 */
final class Tipo extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%tipo}}';
    }
}

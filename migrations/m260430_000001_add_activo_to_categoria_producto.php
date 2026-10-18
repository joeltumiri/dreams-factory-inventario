<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds an `activo` boolean column (1=active, 0=inactive) to {{%categoria}}
 * and {{%producto}} so that records can be enabled/disabled without losing
 * historical data (purchases, sales, lots).
 */
final class m260430_000001_add_activo_to_categoria_producto extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%categoria}}', 'activo', $this->tinyInteger(1)->notNull()->defaultValue(1));
        $this->addColumn('{{%producto}}',  'activo', $this->tinyInteger(1)->notNull()->defaultValue(1));

        // All existing records remain enabled by default
        $this->update('{{%categoria}}', ['activo' => 1]);
        $this->update('{{%producto}}',  ['activo' => 1]);
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%producto}}',  'activo');
        $this->dropColumn('{{%categoria}}', 'activo');
    }
}

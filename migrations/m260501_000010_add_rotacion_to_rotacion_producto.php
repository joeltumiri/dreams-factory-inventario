<?php

declare(strict_types=1);

use yii\db\Migration;

class m260501_000010_add_rotacion_to_rotacion_producto extends Migration
{
    public function safeUp(): void
    {
        // Add decimal rotation field after 'cantidad'
        $this->addColumn('{{%rotacion_producto}}', 'rotacion', $this->decimal(8, 2)->null()->defaultValue(null)->after('cantidad'));
        // Add periodo (month label e.g. "2026-05") for finer granularity
        $this->addColumn('{{%rotacion_producto}}', 'periodo', $this->string(7)->null()->defaultValue(null)->after('gestion'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%rotacion_producto}}', 'rotacion');
        $this->dropColumn('{{%rotacion_producto}}', 'periodo');
    }
}

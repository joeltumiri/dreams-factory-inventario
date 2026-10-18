<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260430_000002_create_conteo_fisico_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%conteo_fisico}}', [
            'id'              => $this->primaryKey(),
            'producto_id'     => $this->integer()->notNull(),
            'cantidad_fisica' => $this->integer()->notNull(),
            'cantidad_sistema'=> $this->integer()->notNull()->defaultValue(0),
            'diferencia'      => $this->integer()->notNull()->defaultValue(0),
            'ajustado'        => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'nota'            => $this->string(255),
            'fecha'           => $this->dateTime()->notNull(),
            'created_by'      => $this->integer(),
            'created_at'      => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

        $this->createIndex('idx_conteo_fisico_producto', '{{%conteo_fisico}}', 'producto_id');
        $this->createIndex('idx_conteo_fisico_fecha', '{{%conteo_fisico}}', 'fecha');
        $this->addForeignKey(
            'fk_conteo_fisico_producto',
            '{{%conteo_fisico}}', 'producto_id',
            '{{%producto}}', 'id',
            'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%conteo_fisico}}');
    }
}

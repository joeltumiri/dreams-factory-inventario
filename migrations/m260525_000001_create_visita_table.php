<?php

use yii\db\Migration;

/**
 * Crea la tabla visita para registrar visitas a clientes con su estado.
 * Estado: 1=Cerrado (rojo), 2=Sin pedido (amarillo), 3=Con pedido (verde)
 */
final class m260525_000001_create_visita_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%visita}}', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer()->notNull()->comment('Vendedor que realizó la visita'),
            'cliente_id' => $this->integer()->notNull(),
            'venta_id'   => $this->integer()->null()->comment('Venta generada (si aplica)'),
            'fecha'      => $this->date()->notNull(),
            'estado'     => $this->tinyInteger()->notNull()->defaultValue(2)
                ->comment('1=Cerrado, 2=Sin pedido, 3=Con pedido'),
            'nota'       => $this->string(500)->null(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_visita_fecha',    '{{%visita}}', 'fecha');
        $this->createIndex('idx_visita_user',     '{{%visita}}', 'user_id');
        $this->createIndex('idx_visita_cliente',  '{{%visita}}', 'cliente_id');

        $this->addForeignKey('fk_visita_user',    '{{%visita}}', 'user_id',    '{{%user}}',    'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_visita_cliente', '{{%visita}}', 'cliente_id', '{{%cliente}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_visita_cliente', '{{%visita}}');
        $this->dropForeignKey('fk_visita_user',    '{{%visita}}');
        $this->dropTable('{{%visita}}');
    }
}

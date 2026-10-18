<?php

use yii\db\Migration;

final class m260430_000005_create_gestion_cierre_and_compra_pago extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        // Estado de pago en compras (1=Pendiente, 2=Pagado por defecto)
        if (!$this->db->getTableSchema('{{%compra}}')->getColumn('estado_pago_id')) {
            $this->addColumn('{{%compra}}', 'estado_pago_id', $this->integer()->notNull()->defaultValue(1)->after('gestion'));
            $this->createIndex('ix_compra_estado_pago', '{{%compra}}', 'estado_pago_id');
            $this->addForeignKey('fk_compra_estado_pago', '{{%compra}}', 'estado_pago_id', '{{%estado_pago}}', 'id', 'RESTRICT', 'CASCADE');
        }

        // Cierre de gestion (anual)
        $this->createTable('{{%gestion_cierre}}', [
            'id'             => $this->primaryKey(),
            'gestion'        => $this->integer()->notNull(),
            'fecha_cierre'   => $this->dateTime()->notNull(),
            'total_compras'  => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'total_ventas'   => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'total_descuentos' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'total_cobrado'  => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'total_pagado'   => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'num_compras'    => $this->integer()->notNull()->defaultValue(0),
            'num_ventas'     => $this->integer()->notNull()->defaultValue(0),
            'margen'         => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'observaciones'  => $this->text()->null(),
            'created_by'     => $this->integer()->null(),
            'created_at'     => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_gestion_cierre_gestion', '{{%gestion_cierre}}', 'gestion', true);
        $this->addForeignKey('fk_gestion_cierre_user', '{{%gestion_cierre}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%gestion_cierre}}');
        if ($this->db->getTableSchema('{{%compra}}')->getColumn('estado_pago_id')) {
            $this->dropForeignKey('fk_compra_estado_pago', '{{%compra}}');
            $this->dropIndex('ix_compra_estado_pago', '{{%compra}}');
            $this->dropColumn('{{%compra}}', 'estado_pago_id');
        }
    }
}

<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260429_000003_create_supplier_product_purchase_lote_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_spanish_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%proveedor}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(150)->notNull(),
            'razon_social' => $this->string(200)->null(),
            'direccion' => $this->string(255)->null(),
            'contacto' => $this->string(255)->null(),
            'nota' => $this->string(255)->null(),
            'nit' => $this->string(30)->null(),
            'email' => $this->string(150)->null(),
            'telefono' => $this->string(30)->null(),
            'ciudad_id' => $this->integer()->null(),
            'rubro' => $this->string(150)->null(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_proveedor_nombre', '{{%proveedor}}', 'nombre', true);
        $this->createIndex('ix_proveedor_ciudad_id', '{{%proveedor}}', 'ciudad_id');
        $this->addForeignKey('fk_proveedor_ciudad', '{{%proveedor}}', 'ciudad_id', '{{%ciudad}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_proveedor_created_by', '{{%proveedor}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_proveedor_updated_by', '{{%proveedor}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%producto}}', [
            'id' => $this->primaryKey(),
            'numero' => $this->integer()->null(),
            'codigo' => $this->string(32)->null(),
            'nombre' => $this->string(200)->notNull(),
            'slug' => $this->string(220)->null(),
            'descripcion' => $this->text()->null(),
            'precio_compra' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'precio_venta' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'stock' => $this->integer()->notNull()->defaultValue(0),
            'stock_minimo' => $this->integer()->notNull()->defaultValue(0),
            'categoria_id' => $this->integer()->notNull(),
            'nota' => $this->string(255)->null(),
            'producto_cant' => $this->integer()->null(),
            'producto_recibo' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_producto_codigo', '{{%producto}}', 'codigo', true);
        $this->createIndex('ux_producto_slug', '{{%producto}}', 'slug', true);
        $this->createIndex('ix_producto_categoria_id', '{{%producto}}', 'categoria_id');
        $this->addForeignKey('fk_producto_categoria', '{{%producto}}', 'categoria_id', '{{%categoria}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_producto_created_by', '{{%producto}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_producto_updated_by', '{{%producto}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%compra}}', [
            'id' => $this->primaryKey(),
            'proveedor_id' => $this->integer()->notNull(),
            'fecha_compra' => $this->date()->notNull(),
            'nota' => $this->string(255)->null(),
            'importe_total' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'documento' => $this->string(50)->null(),
            'nro_documento' => $this->string(80)->null(),
            'gestion' => $this->integer()->notNull(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_compra_proveedor_id', '{{%compra}}', 'proveedor_id');
        $this->createIndex('ix_compra_gestion', '{{%compra}}', 'gestion');
        $this->addForeignKey('fk_compra_proveedor', '{{%compra}}', 'proveedor_id', '{{%proveedor}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_compra_created_by', '{{%compra}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_compra_updated_by', '{{%compra}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%detalle_compra}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'cantidad' => $this->integer()->notNull(),
            'precio_unitario' => $this->decimal(10, 2)->notNull(),
            'importe' => $this->decimal(12, 2)->notNull(),
            'categoria_id' => $this->integer()->notNull(),
            'fecha_vencimiento' => $this->date()->null(),
            'fecha_elaboracion' => $this->date()->null(),
            'compra_id' => $this->integer()->notNull(),
            'proveedor_id' => $this->integer()->notNull(),
            'almacen_id' => $this->integer()->notNull(),
            'gestion' => $this->integer()->notNull(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_detalle_compra_compra_id', '{{%detalle_compra}}', 'compra_id');
        $this->createIndex('ix_detalle_compra_producto_id', '{{%detalle_compra}}', 'producto_id');
        $this->createIndex('ix_detalle_compra_proveedor_id', '{{%detalle_compra}}', 'proveedor_id');
        $this->createIndex('ix_detalle_compra_almacen_id', '{{%detalle_compra}}', 'almacen_id');
        $this->addForeignKey('fk_detalle_compra_compra', '{{%detalle_compra}}', 'compra_id', '{{%compra}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_producto', '{{%detalle_compra}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_categoria', '{{%detalle_compra}}', 'categoria_id', '{{%categoria}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_proveedor', '{{%detalle_compra}}', 'proveedor_id', '{{%proveedor}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_almacen', '{{%detalle_compra}}', 'almacen_id', '{{%almacen}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_created_by', '{{%detalle_compra}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_detalle_compra_updated_by', '{{%detalle_compra}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%lote}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'compra_id' => $this->integer()->notNull(),
            'detalle_compra_id' => $this->integer()->notNull(),
            'almacen_id' => $this->integer()->notNull(),
            'fecha_ingreso' => $this->dateTime()->notNull(),
            'fecha_vencimiento' => $this->date()->null(),
            'cantidad_inicial' => $this->integer()->notNull(),
            'cantidad_disponible' => $this->integer()->notNull(),
            'precio_compra_unitario' => $this->decimal(10, 2)->notNull(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'gestion' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_lote_producto_id', '{{%lote}}', 'producto_id');
        $this->createIndex('ix_lote_fecha_ingreso', '{{%lote}}', 'fecha_ingreso');
        $this->addForeignKey('fk_lote_producto', '{{%lote}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_lote_compra', '{{%lote}}', 'compra_id', '{{%compra}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_lote_detalle_compra', '{{%lote}}', 'detalle_compra_id', '{{%detalle_compra}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_lote_almacen', '{{%lote}}', 'almacen_id', '{{%almacen}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_lote_created_by', '{{%lote}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_lote_updated_by', '{{%lote}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%lote}}');
        $this->dropTable('{{%detalle_compra}}');
        $this->dropTable('{{%compra}}');
        $this->dropTable('{{%producto}}');
        $this->dropTable('{{%proveedor}}');
    }
}

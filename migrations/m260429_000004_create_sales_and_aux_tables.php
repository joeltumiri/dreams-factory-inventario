<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260429_000004_create_sales_and_aux_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_spanish_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cliente}}', [
            'id' => $this->primaryKey(),
            'propietario' => $this->string(150)->null(),
            'razon_social' => $this->string(200)->null(),
            'zona' => $this->string(120)->null(),
            'direccion' => $this->string(255)->null(),
            'contacto' => $this->string(255)->null(),
            'geo' => $this->text()->null(),
            'departamento_id' => $this->integer()->notNull(),
            'ciudad_id' => $this->integer()->notNull(),
            'tipo_id' => $this->integer()->notNull(),
            'nit' => $this->string(30)->null(),
            'slug' => $this->string(220)->null(),
            'nombre_factura' => $this->string(200)->null(),
            'latitud' => $this->decimal(10, 7)->null(),
            'longitud' => $this->decimal(10, 7)->null(),
            'iframe' => $this->text()->null(),
            'email' => $this->string(150)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_cliente_slug', '{{%cliente}}', 'slug', true);
        $this->createIndex('ix_cliente_ciudad_id', '{{%cliente}}', 'ciudad_id');
        $this->createIndex('ix_cliente_tipo_id', '{{%cliente}}', 'tipo_id');
        $this->addForeignKey('fk_cliente_departamento', '{{%cliente}}', 'departamento_id', '{{%departamento}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_cliente_ciudad', '{{%cliente}}', 'ciudad_id', '{{%ciudad}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_cliente_tipo', '{{%cliente}}', 'tipo_id', '{{%tipo}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_cliente_created_by', '{{%cliente}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_cliente_updated_by', '{{%cliente}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%precio_venta}}', [
            'id' => $this->primaryKey(),
            'precio' => $this->decimal(10, 2)->notNull(),
            'obs' => $this->string(255)->null(),
            'producto_id' => $this->integer()->notNull(),
            'ciudad_id' => $this->integer()->notNull(),
            'departamento_id' => $this->integer()->notNull(),
            'precio_original' => $this->decimal(10, 2)->null(),
            'precio_oferta' => $this->decimal(10, 2)->null(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_precio_venta_producto', '{{%precio_venta}}', 'producto_id');
        $this->createIndex('ix_precio_venta_ciudad', '{{%precio_venta}}', 'ciudad_id');
        $this->addForeignKey('fk_precio_venta_producto', '{{%precio_venta}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_precio_venta_ciudad', '{{%precio_venta}}', 'ciudad_id', '{{%ciudad}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_precio_venta_departamento', '{{%precio_venta}}', 'departamento_id', '{{%departamento}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_precio_venta_created_by', '{{%precio_venta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_precio_venta_updated_by', '{{%precio_venta}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%oferta}}', [
            'id' => $this->primaryKey(),
            'fecha_inicio' => $this->date()->notNull(),
            'fecha_final' => $this->date()->notNull(),
            'producto_id' => $this->integer()->notNull(),
            'precio_venta' => $this->decimal(10, 2)->notNull(),
            'precio_oferta' => $this->decimal(10, 2)->notNull(),
            'obs' => $this->string(255)->null(),
            'ciudad_id' => $this->integer()->notNull(),
            'departamento_id' => $this->integer()->notNull(),
            'precio_venta_id' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_oferta_producto', '{{%oferta}}', 'producto_id');
        $this->addForeignKey('fk_oferta_producto', '{{%oferta}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_oferta_ciudad', '{{%oferta}}', 'ciudad_id', '{{%ciudad}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_oferta_departamento', '{{%oferta}}', 'departamento_id', '{{%departamento}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_oferta_precio_venta', '{{%oferta}}', 'precio_venta_id', '{{%precio_venta}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_oferta_created_by', '{{%oferta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_oferta_updated_by', '{{%oferta}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%item_oferta}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'cantidad' => $this->integer()->notNull(),
            'precio_oferta' => $this->decimal(10, 2)->notNull(),
            'importe' => $this->decimal(12, 2)->notNull(),
            'oferta_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_item_oferta_oferta', '{{%item_oferta}}', 'oferta_id');
        $this->addForeignKey('fk_item_oferta_oferta', '{{%item_oferta}}', 'oferta_id', '{{%oferta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_item_oferta_producto', '{{%item_oferta}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_item_oferta_created_by', '{{%item_oferta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_item_oferta_updated_by', '{{%item_oferta}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%venta}}', [
            'id' => $this->primaryKey(),
            'codigo' => $this->string(40)->notNull(),
            'fecha_pedido' => $this->date()->notNull(),
            'fecha_entrega' => $this->date()->null(),
            'estado_venta_id' => $this->integer()->notNull(),
            'nota' => $this->string(255)->null(),
            'tipo_pago_id' => $this->integer()->notNull(),
            'cliente_id' => $this->integer()->notNull(),
            'gestion' => $this->integer()->notNull(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'user_id' => $this->integer()->notNull(),
            'estado_pago_id' => $this->integer()->notNull(),
            'importe_total' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'descuento' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'total_efectivo' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'nota_descuento' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_venta_codigo', '{{%venta}}', 'codigo', true);
        $this->createIndex('ix_venta_cliente', '{{%venta}}', 'cliente_id');
        $this->createIndex('ix_venta_user', '{{%venta}}', 'user_id');
        $this->addForeignKey('fk_venta_estado_venta', '{{%venta}}', 'estado_venta_id', '{{%estado_venta}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_venta_tipo_pago', '{{%venta}}', 'tipo_pago_id', '{{%tipo_pago}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_venta_cliente', '{{%venta}}', 'cliente_id', '{{%cliente}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_venta_user', '{{%venta}}', 'user_id', '{{%user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_venta_estado_pago', '{{%venta}}', 'estado_pago_id', '{{%estado_pago}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_venta_created_by', '{{%venta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_venta_updated_by', '{{%venta}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%detalle_venta}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'producto_recibo_venta' => $this->string(255)->null(),
            'precio_unitario' => $this->decimal(10, 2)->notNull(),
            'precio_real' => $this->decimal(10, 2)->null(),
            'cantidad' => $this->integer()->notNull(),
            'importe' => $this->decimal(12, 2)->notNull(),
            'venta_id' => $this->integer()->notNull(),
            'estado_venta_id' => $this->integer()->notNull(),
            'tipo_pago_id' => $this->integer()->notNull(),
            'cliente_id' => $this->integer()->notNull(),
            'gestion' => $this->integer()->notNull(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'user_id' => $this->integer()->notNull(),
            'estado_pago_id' => $this->integer()->notNull(),
            'descripcion' => $this->string(255)->null(),
            'precio_venta_id' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_detalle_venta_venta', '{{%detalle_venta}}', 'venta_id');
        $this->createIndex('ix_detalle_venta_producto', '{{%detalle_venta}}', 'producto_id');
        $this->addForeignKey('fk_detalle_venta_venta', '{{%detalle_venta}}', 'venta_id', '{{%venta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_producto', '{{%detalle_venta}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_estado_venta', '{{%detalle_venta}}', 'estado_venta_id', '{{%estado_venta}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_tipo_pago', '{{%detalle_venta}}', 'tipo_pago_id', '{{%tipo_pago}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_cliente', '{{%detalle_venta}}', 'cliente_id', '{{%cliente}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_user', '{{%detalle_venta}}', 'user_id', '{{%user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_estado_pago', '{{%detalle_venta}}', 'estado_pago_id', '{{%estado_pago}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_precio_venta', '{{%detalle_venta}}', 'precio_venta_id', '{{%precio_venta}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_created_by', '{{%detalle_venta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_detalle_venta_updated_by', '{{%detalle_venta}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        // FIFO movements (FK a detalle_venta)
        $this->createTable('{{%movimiento_fifo}}', [
            'id' => $this->primaryKey(),
            'lote_id' => $this->integer()->notNull(),
            'detalle_venta_id' => $this->integer()->notNull(),
            'cantidad_salida' => $this->integer()->notNull(),
            'precio_compra_unitario' => $this->decimal(10, 2)->notNull(),
            'fecha_salida' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_mov_fifo_lote', '{{%movimiento_fifo}}', 'lote_id');
        $this->createIndex('ix_mov_fifo_detalle_venta', '{{%movimiento_fifo}}', 'detalle_venta_id');
        $this->addForeignKey('fk_mov_fifo_lote', '{{%movimiento_fifo}}', 'lote_id', '{{%lote}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_mov_fifo_detalle_venta', '{{%movimiento_fifo}}', 'detalle_venta_id', '{{%detalle_venta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_mov_fifo_created_by', '{{%movimiento_fifo}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%cierre_compra}}', [
            'id' => $this->primaryKey(),
            'gestion' => $this->integer()->notNull(),
            'compra_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_cierre_compra_compra', '{{%cierre_compra}}', 'compra_id', '{{%compra}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_cierre_compra_created_by', '{{%cierre_compra}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%cierre_venta}}', [
            'id' => $this->primaryKey(),
            'gestion' => $this->integer()->notNull(),
            'venta_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_cierre_venta_venta', '{{%cierre_venta}}', 'venta_id', '{{%venta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_cierre_venta_created_by', '{{%cierre_venta}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%visita}}', [
            'id' => $this->primaryKey(),
            'fecha_agenda' => $this->date()->null(),
            'fecha_visita' => $this->date()->null(),
            'ultima_visita' => $this->date()->null(),
            'user_id' => $this->integer()->notNull(),
            'cliente_id' => $this->integer()->notNull(),
            'estado_visita_id' => $this->integer()->notNull(),
            'latitud' => $this->decimal(10, 7)->null(),
            'longitud' => $this->decimal(10, 7)->null(),
            'obs' => $this->string(255)->null(),
            'geo' => $this->text()->null(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(1),
            'hora_visita' => $this->time()->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_visita_user', '{{%visita}}', 'user_id', '{{%user}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_visita_cliente', '{{%visita}}', 'cliente_id', '{{%cliente}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_visita_estado_visita', '{{%visita}}', 'estado_visita_id', '{{%estado_visita}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_visita_created_by', '{{%visita}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_visita_updated_by', '{{%visita}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%transaccion}}', [
            'id' => $this->primaryKey(),
            'venta_id' => $this->integer()->notNull(),
            'efectivo' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'saldo' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'documento' => $this->string(80)->null(),
            'user_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_transaccion_venta', '{{%transaccion}}', 'venta_id', '{{%venta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_transaccion_user', '{{%transaccion}}', 'user_id', '{{%user}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%rotacion_producto}}', [
            'id' => $this->primaryKey(),
            'producto_id' => $this->integer()->notNull(),
            'cantidad' => $this->integer()->null(),
            'stock' => $this->integer()->null(),
            'almacen_id' => $this->integer()->notNull(),
            'productos_entregados' => $this->integer()->null(),
            'productos_confirmados' => $this->integer()->null(),
            'productos_pendientes' => $this->integer()->null(),
            'productos_enviados' => $this->integer()->null(),
            'gestion' => $this->integer()->null(),
            'total_fisico' => $this->integer()->null(),
            'diferencia' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_rotacion_producto_producto', '{{%rotacion_producto}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_rotacion_producto_almacen', '{{%rotacion_producto}}', 'almacen_id', '{{%almacen}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%qr}}', [
            'id' => $this->primaryKey(),
            'gestion' => $this->integer()->notNull(),
            'direccion' => $this->string(255)->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_qr_created_by', '{{%qr}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        // Relación venta <-> oferta (campos mínimos, porque la guía no detalla)
        $this->createTable('{{%venta_oferta}}', [
            'id' => $this->primaryKey(),
            'venta_id' => $this->integer()->notNull(),
            'oferta_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_venta_oferta_venta', '{{%venta_oferta}}', 'venta_id', '{{%venta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_venta_oferta_oferta', '{{%venta_oferta}}', 'oferta_id', '{{%oferta}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%detalle_oferta}}', [
            'id' => $this->primaryKey(),
            'venta_oferta_id' => $this->integer()->notNull(),
            'producto_id' => $this->integer()->notNull(),
            'cantidad' => $this->integer()->notNull(),
            'precio_oferta' => $this->decimal(10, 2)->notNull(),
            'importe' => $this->decimal(12, 2)->notNull(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->addForeignKey('fk_detalle_oferta_venta_oferta', '{{%detalle_oferta}}', 'venta_oferta_id', '{{%venta_oferta}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_detalle_oferta_producto', '{{%detalle_oferta}}', 'producto_id', '{{%producto}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%detalle_oferta}}');
        $this->dropTable('{{%venta_oferta}}');
        $this->dropTable('{{%qr}}');
        $this->dropTable('{{%rotacion_producto}}');
        $this->dropTable('{{%transaccion}}');
        $this->dropTable('{{%visita}}');
        $this->dropTable('{{%cierre_venta}}');
        $this->dropTable('{{%cierre_compra}}');
        $this->dropTable('{{%movimiento_fifo}}');
        $this->dropTable('{{%detalle_venta}}');
        $this->dropTable('{{%venta}}');
        $this->dropTable('{{%item_oferta}}');
        $this->dropTable('{{%oferta}}');
        $this->dropTable('{{%precio_venta}}');
        $this->dropTable('{{%cliente}}');
    }
}

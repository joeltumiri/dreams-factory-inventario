<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260429_000010_remove_unidad_table_and_columns extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->schema;

        $producto = $schema->getTableSchema('{{%producto}}', true);
        if ($producto !== null && isset($producto->columns['unidad_id'])) {
            // drop FK + index if they exist
            try {
                $this->dropForeignKey('fk_producto_unidad', '{{%producto}}');
            } catch (Throwable $e) {
            }
            try {
                $this->dropIndex('ix_producto_unidad_id', '{{%producto}}');
            } catch (Throwable $e) {
            }

            $this->dropColumn('{{%producto}}', 'unidad_id');
        }

        $detalleCompra = $schema->getTableSchema('{{%detalle_compra}}', true);
        if ($detalleCompra !== null && isset($detalleCompra->columns['unidad_id'])) {
            try {
                $this->dropForeignKey('fk_detalle_compra_unidad', '{{%detalle_compra}}');
            } catch (Throwable $e) {
            }

            $this->dropColumn('{{%detalle_compra}}', 'unidad_id');
        }

        $unidad = $schema->getTableSchema('{{%unidad}}', true);
        if ($unidad !== null) {
            $this->dropTable('{{%unidad}}');
        }
    }

    public function safeDown(): void
    {
        $this->stdout("m260429_000010_remove_unidad_table_and_columns cannot be reverted.\n");
        return;
    }
}

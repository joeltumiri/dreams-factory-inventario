<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Agrega documento, nro_documento y metodo_pago a la tabla venta.
 */
final class m260501_000001_add_documento_metodo_pago_to_venta extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->getTableSchema('{{%venta}}');

        if (!$schema->getColumn('documento')) {
            $this->addColumn('{{%venta}}', 'documento', $this->string(30)->null()->after('nota'));
        }
        if (!$schema->getColumn('nro_documento')) {
            $this->addColumn('{{%venta}}', 'nro_documento', $this->string(80)->null()->after('documento'));
        }
        if (!$schema->getColumn('metodo_pago')) {
            $this->addColumn('{{%venta}}', 'metodo_pago', $this->string(30)->null()->after('nro_documento'));
        }
    }

    public function safeDown(): void
    {
        $schema = $this->db->getTableSchema('{{%venta}}');

        if ($schema->getColumn('metodo_pago')) {
            $this->dropColumn('{{%venta}}', 'metodo_pago');
        }
        if ($schema->getColumn('nro_documento')) {
            $this->dropColumn('{{%venta}}', 'nro_documento');
        }
        if ($schema->getColumn('documento')) {
            $this->dropColumn('{{%venta}}', 'documento');
        }
    }
}

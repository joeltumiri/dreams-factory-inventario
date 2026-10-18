<?php

use yii\db\Migration;

class m260529_000001_add_fecha_envio_entrega_to_venta extends Migration
{
    public function up(): void
    {
        $this->addColumn('{{%venta}}', 'fecha_envio',         $this->dateTime()->null()->after('fecha_entrega'));
        $this->addColumn('{{%venta}}', 'fecha_entrega_real',  $this->dateTime()->null()->after('fecha_envio'));
    }

    public function down(): void
    {
        $this->dropColumn('{{%venta}}', 'fecha_entrega_real');
        $this->dropColumn('{{%venta}}', 'fecha_envio');
    }
}

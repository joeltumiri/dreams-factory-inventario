<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260430_000003_add_celular_to_proveedor extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%proveedor}}', 'celular', $this->string(30)->null()->after('telefono'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%proveedor}}', 'celular');
    }
}

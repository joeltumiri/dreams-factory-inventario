<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260429_000001_create_catalog_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_spanish_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%role}}', [
            'id' => $this->primaryKey(),
            'role_name' => $this->string(80)->notNull(),
            'role_value' => $this->smallInteger()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_role_value', '{{%role}}', 'role_value', true);

        $this->createTable('{{%status}}', [
            'id' => $this->primaryKey(),
            'status_name' => $this->string(80)->notNull(),
            'status_value' => $this->smallInteger()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_status_value', '{{%status}}', 'status_value', true);

        $this->createTable('{{%user_type}}', [
            'id' => $this->primaryKey(),
            'user_type_name' => $this->string(80)->notNull(),
            'user_type_value' => $this->smallInteger()->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_user_type_value', '{{%user_type}}', 'user_type_value', true);

        $this->createTable('{{%gender}}', [
            'id' => $this->primaryKey(),
            'gender_name' => $this->string(30)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_gender_name', '{{%gender}}', 'gender_name', true);

        $this->createTable('{{%departamento}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
            'sigla' => $this->string(10)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_departamento_sigla', '{{%departamento}}', 'sigla', true);

        $this->createTable('{{%estado_venta}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_estado_venta_nombre', '{{%estado_venta}}', 'nombre', true);

        $this->createTable('{{%estado_pago}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_estado_pago_nombre', '{{%estado_pago}}', 'nombre', true);

        $this->createTable('{{%tipo_pago}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_tipo_pago_nombre', '{{%tipo_pago}}', 'nombre', true);

        $this->createTable('{{%estado_visita}}', [
            'id' => $this->primaryKey(),
            'estado' => $this->string(80)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_estado_visita_estado', '{{%estado_visita}}', 'estado', true);

        // tipos de cliente (farmacia / tienda)
        $this->createTable('{{%tipo}}', [
            'id' => $this->primaryKey(),
            'farmacia' => $this->string(80)->notNull(),
        ], $tableOptions);
        $this->createIndex('ux_tipo_farmacia', '{{%tipo}}', 'farmacia', true);

        $now = time();

        $this->batchInsert('{{%role}}', ['role_name', 'role_value'], [
            ['Gerente', 1],
            ['Encargado de Almacén', 2],
            ['Vendedor', 3],
        ]);

        $this->batchInsert('{{%status}}', ['status_name', 'status_value'], [
            ['Activo', 1],
            ['Inactivo', 0],
        ]);

        $this->batchInsert('{{%user_type}}', ['user_type_name', 'user_type_value'], [
            ['Gerente', 1],
            ['Almacén', 2],
            ['Vendedor', 3],
        ]);

        $this->batchInsert('{{%gender}}', ['gender_name'], [
            ['Masculino'],
            ['Femenino'],
        ]);

        $this->batchInsert('{{%departamento}}', ['nombre', 'sigla'], [
            ['La Paz', 'LP'],
        ]);


        $this->batchInsert('{{%estado_venta}}', ['nombre'], [
            ['Para Entregar'],
            ['Enviadas'],
            ['Entregadas'],
        ]);

        $this->batchInsert('{{%estado_pago}}', ['nombre'], [
            ['Pendiente'],
            ['Pagado'],
        ]);

        $this->batchInsert('{{%tipo_pago}}', ['nombre'], [
            ['Efectivo'],
            ['Crédito'],
            ['Transferencia'],
        ]);

        $this->batchInsert('{{%estado_visita}}', ['estado'], [
            ['Pendiente'],
            ['Realizada'],
            ['Cancelada'],
        ]);

        $this->batchInsert('{{%tipo}}', ['farmacia'], [
            ['Farmacia'],
            ['Tienda'],
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%tipo}}');
        $this->dropTable('{{%estado_visita}}');
        $this->dropTable('{{%tipo_pago}}');
        $this->dropTable('{{%estado_pago}}');
        $this->dropTable('{{%estado_venta}}');
        $this->dropTable('{{%departamento}}');
        $this->dropTable('{{%gender}}');
        $this->dropTable('{{%user_type}}');
        $this->dropTable('{{%status}}');
        $this->dropTable('{{%role}}');
    }
}

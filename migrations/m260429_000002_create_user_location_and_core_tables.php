<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260429_000002_create_user_location_and_core_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_spanish_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'email' => $this->string(150)->null(),

            'status_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
            'user_type_id' => $this->integer()->notNull(),

            'foto' => $this->string(255)->null(),
            'nombre' => $this->string(80)->null(),
            'paterno' => $this->string(80)->null(),
            'materno' => $this->string(80)->null(),
            'ci' => $this->string(30)->null(),
            'slug' => $this->string(120)->null(),
            'fono' => $this->string(30)->null(),
            'direccion' => $this->string(255)->null(),

            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_user_username', '{{%user}}', 'username', true);
        $this->createIndex('ux_user_email', '{{%user}}', 'email', true);
        $this->createIndex('ux_user_slug', '{{%user}}', 'slug', true);
        $this->createIndex('ix_user_role_id', '{{%user}}', 'role_id');
        $this->createIndex('ix_user_status_id', '{{%user}}', 'status_id');

        $this->addForeignKey('fk_user_role', '{{%user}}', 'role_id', '{{%role}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_user_status', '{{%user}}', 'status_id', '{{%status}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_user_user_type', '{{%user}}', 'user_type_id', '{{%user_type}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%profile}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'first_name' => $this->string(80)->null(),
            'last_name' => $this->string(80)->null(),
            'birthdate' => $this->date()->null(),
            'gender_id' => $this->integer()->null(),
            'ci' => $this->string(30)->null(),
            'slug' => $this->string(120)->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_profile_user_id', '{{%profile}}', 'user_id', true);
        $this->createIndex('ux_profile_slug', '{{%profile}}', 'slug', true);
        $this->addForeignKey('fk_profile_user', '{{%profile}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_profile_gender', '{{%profile}}', 'gender_id', '{{%gender}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%ciudad}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(80)->notNull(),
            'departamento_id' => $this->integer()->notNull(),
            'descripcion' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ix_ciudad_departamento_id', '{{%ciudad}}', 'departamento_id');
        $this->createIndex('ix_ciudad_created_by', '{{%ciudad}}', 'created_by');
        $this->addForeignKey('fk_ciudad_departamento', '{{%ciudad}}', 'departamento_id', '{{%departamento}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_ciudad_created_by', '{{%ciudad}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%almacen}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(120)->notNull(),
            'direccion' => $this->string(255)->null(),
            'contacto' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_almacen_nombre', '{{%almacen}}', 'nombre', true);
        $this->addForeignKey('fk_almacen_created_by', '{{%almacen}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_almacen_updated_by', '{{%almacen}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%categoria}}', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string(120)->notNull(),
            'nota' => $this->string(255)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('ux_categoria_nombre', '{{%categoria}}', 'nombre', true);
        $this->addForeignKey('fk_categoria_created_by', '{{%categoria}}', 'created_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_categoria_updated_by', '{{%categoria}}', 'updated_by', '{{%user}}', 'id', 'SET NULL', 'CASCADE');

        $now = time();

        // Usuarios demo para poder iniciar sesión
        $security = Yii::$app->security;
        $this->insert('{{%user}}', [
            'username' => 'gerente',
            'auth_key' => $security->generateRandomString(32),
            'password_hash' => $security->generatePasswordHash('123456'),
            'email' => 'admin@example.com',
            'status_id' => 1,
            'role_id' => 1,
            'user_type_id' => 1,
            'nombre' => 'Gerente',
            'paterno' => 'Dreams',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insert('{{%user}}', [
            'username' => 'almacen',
            'auth_key' => $security->generateRandomString(32),
            'password_hash' => $security->generatePasswordHash('123456'),
            'email' => 'almacen@example.com',
            'status_id' => 1,
            'role_id' => 2,
            'user_type_id' => 2,
            'nombre' => 'Encargado',
            'paterno' => 'Almacén',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->insert('{{%user}}', [
            'username' => 'vendedor',
            'auth_key' => $security->generateRandomString(32),
            'password_hash' => $security->generatePasswordHash('123456'),
            'email' => 'ventas@example.com',
            'status_id' => 1,
            'role_id' => 3,
            'user_type_id' => 3,
            'nombre' => 'Vendedor',
            'paterno' => 'Dreams',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Ciudad base
        $this->insert('{{%ciudad}}', [
            'nombre' => 'El Alto',
            'departamento_id' => 1,
            'descripcion' => 'Ciudad base del sistema',
            'created_by' => 1,
            'created_at' => $now,
        ]);

        // Almacén base
        $this->insert('{{%almacen}}', [
            'nombre' => 'Almacén Principal',
            'direccion' => 'Villa Dolores, El Alto',
            'contacto' => null,
            'created_by' => 1,
            'created_at' => $now,
        ]);

        // Categorías precargadas (9)
        $this->batchInsert('{{%categoria}}', ['nombre', 'nota', 'created_by', 'created_at'], [
            ['Higiene y Cuidado del Bebé', 'Pañales, toallitas, cremas, champú, jabón, colonias, termómetros', 1, $now],
            ['Ropa y Accesorios Textiles', 'Bodies, pijamas, medias, gorros, baberos, mantas', 1, $now],
            ['Descanso y Dormitorio', 'Cunas, colchones, sábanas, almohadas, mosquiteros', 1, $now],
            ['Juguetes y Estimulación', 'Sonajeros, mordedores, gimnasios, juguetes didácticos', 1, $now],
            ['Alimentación No Comestible', 'Biberones, chupones, esterilizadores, calentadores, vajilla infantil', 1, $now],
            ['Transporte y Paseo', 'Coches, sillas para auto, mochilas portabebé, bolsos pañaleros', 1, $now],
            ['Seguridad y Salud', 'Monitores, protectores, barreras, aspiradores nasales', 1, $now],
            ['Muebles y Equipamiento', 'Sillas de comer, cambiadores, corralitos', 1, $now],
            ['Accesorios Varios', 'Organizadores, decoración infantil, regalos', 1, $now],
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%categoria}}');
        $this->dropTable('{{%almacen}}');
        $this->dropTable('{{%ciudad}}');
        $this->dropTable('{{%profile}}');
        $this->dropTable('{{%user}}');
    }
}

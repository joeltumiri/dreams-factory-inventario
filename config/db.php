<?php

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '';
$dbName = getenv('DB_NAME') ?: 'almacen';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$portPart = $dbPort !== '' ? ";port={$dbPort}" : '';

return [
    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host={$dbHost}{$portPart};dbname={$dbName}",
    'username' => $dbUser,
    'password' => $dbPass,
    'charset' => 'utf8',

    // Fuerza colación en la conexión (MariaDB/MySQL)
    'on afterOpen' => static function ($event): void {
        $event->sender->createCommand('SET NAMES utf8 COLLATE utf8_spanish_ci')->execute();
    },

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

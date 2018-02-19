<?php

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'dbmigrations\commands',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
    'aliases' => [
        '@dbmigrations' => dirname(__DIR__),
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'templateFile' => '@dbmigrations/templates/migration.php',
        ],
    ]
];

return $config;

<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
    'rbac' => [
        'class' => 'yii2mod\rbac\ConsoleModule',
    ],
],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/error.log',
                    'maxFileSize' => 1024,
                    'maxLogFiles' => 3,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace'],
                    'logFile' => '@app/runtime/logs/app.log',
                    'maxFileSize' => 1024,
                    'maxLogFiles' => 3,
                ],
            ],
        ],
    'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
        ],
        'db' => $db,
            'redis' => [
            'class' => \yii\redis\Connection::class,
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue_fcm',
            'serializer' => \yii\queue\serializers\JsonSerializer::class,
            'ttr' => 30,
            'attempts' => 3,
        ],
        'firebaseService' => [
            'class' => 'app\component\FirebaseService',
        ],
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

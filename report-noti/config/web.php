<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'language' => 'vi',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@helpers' => '@app/helpers',
    ],
    // 'layout' => 'adminlte.php',
    'defaultRoute' => '/dash-board',
    'modules' => [
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
        ],
        'api' => [
            'class' => 'app\modules\api\api',
        ],
        'cronjob' => [
            'class' => 'app\modules\cronjob\cronjob',
        ],
        'google' => [
            'class' => 'app\modules\google\google',
        ],
        'notification' => [
            'class' => 'app\modules\notification\notification',
        ],
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    // '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                    '@app/views' => '@app/themes/adminlte',
                    'baseUrl' => '@web/../themes/adminlte',
                ],
            ],
        ],
        'class' => 'helpers\MyHelper',
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'nS3mdgcIIwcro37-r9an-4b6d4vPzMPm',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Admin',
            'loginUrl' => ['/admin/login'],
            'enableAutoLogin' => false,
            'authTimeout' => 60 * 60 * 24 * 30,
        ],
        'errorHandler' => [
            'errorAction' => 'admin/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'trip/transfer/<id:\d+>' => 'trip/transfer',
                'message-zns/get-message/<id:\d+>' => 'message-zns/get-message',
                'statistic/update-status/<id:\d+>' => 'statistic/update-status',
                'driver/update-status/<id:\d+>' => 'driver/update-status',
                'driver/accept-driver-sub/<id:\d+>/<status:\d+>' => 'driver/accept-driver-sub',
                'trip/update/<id:\d+>' => 'trip/update',
                'statistic/check-new-booking' => 'statistic/check-new-booking',
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
        ],
        'i18n' => [
            'translations' => [
                'yii2mod.rbac' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2mod/rbac/messages',
                ],
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],
        'tripService' => [
            'class' => 'app\services\TripService',
        ],
        'sendMessageZnsService' => [
            'class' => 'app\services\SendMessageZnsService',
        ],
        'userLogger' => [
            'class' => 'app\component\UserLogger',
        ],
        'apiLogger' => [
            'class' => 'app\component\ApiLogger',
        ],
        'firebaseService' => [
            'class' => 'app\component\FirebaseService',
        ],
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
    ],
    'as access' => [
        'class' => yii2mod\rbac\filters\AccessControl::class,
        'allowActions' => [
            'admin/login',
            'api/client/catch',
            'api/auth/login',
            'api/pay-transaction/list',
            'api/pay-transaction/recharge',
            'api/pay-transaction/minus-system',
            'api/booking/list',
            'system-configuration/refresh-token',
            // 'rbac/*',
            'debug/*',
            'api/request-call-back/create-call-back',
            'cronjob/message-zns/notify-driver',
            'cronjob/summary-report/generate',
            'cronjob/driver/update-point',
            'cronjob/bid-image-cleanup/old',
            'location-configuration/search',
            'google/google-excel/insert',
            'api/agency/register',
            'api/search/address-start',
            'api/search/address-end',
            'api/search/schedule',
            'api/search/price',
            'api/search/find-price',
            'api/search/type-of-car',
            'api/search/type-reject',
            'api/profile/detail',
            'api/google-map/google-maps-proxy',
            'notification/driver/send',
            'notification/driver/save-token',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '::1'],
    ];
}

return $config;

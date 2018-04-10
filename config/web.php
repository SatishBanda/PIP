<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'acars',
    'name' => 'ACARS',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'America/New_York',
    'bootstrap' => ['log', 'app\components\Aliases'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'K0I9yOJPLBqbaam4IWrqtelfxp1m1zEXB04f5H6D',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'formGenerationComponent' => [
            'class' => 'app\components\FormGeneration',
        ],
        'eFile' => [
            'class' => 'app\components\EfileComponent',
        ],
        'sharefile' => [
            'class' => 'app\components\SharefileComponent',
        ],
        'pdfGeneration' => [
            'class' => 'mikehaertl\wkhtmlto\Pdf',
            'ignoreWarnings' => 'true',
            'binary' => 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe',

        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        /* 'authManager' => [
             'class' => 'yii\rbac\DbManager',
         ],*/
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'sendGrid' => [
            'class' => 'bryglen\sendgrid\Mailer',
            'username' => 'samknara',
            'password' => 'SkyTech2017#@#',
            //'viewPath' => '@app/views/mail', // your view path here
        ],
        'log' => [
            'flushInterval' => 100,
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => require(__DIR__ . '/log_targets.php'),
        ],

        'db' => require(__DIR__ . '/db.php'),

        'urlManager' => require(__DIR__ . '/routes.php'),

        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {

                $response = $event->sender;
                $response->headers->set('cache-control', 'no-cache');
                if ($response->format == 'html') {
                    return $response;
                }

                $responseData = $response->data;

                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }


                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];

                }
                return $response;
            },
        ],

    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

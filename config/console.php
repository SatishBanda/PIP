<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'America/New_York', 
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],    	
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
		 'urlManager' => [
            'class' => 'yii\web\UrlManager',//http://198.90.22.116/v1/file-processing/get-archived-documents
            'scriptUrl' => "http://aca.localhost", // Setup your domain
            'baseUrl' => "http://aca.localhost", // Setup your domain
            'hostInfo' => "http://aca.localhost", // Setup your domain
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            // ...
        ],
        'db' => $db,        
    ],
    'params' => $params,   
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

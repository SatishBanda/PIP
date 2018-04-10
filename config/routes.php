<?php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        'ping' => 'site/ping',
        'maintenance' => 'site/maintenance',
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/user',
            'pluralize' => false,
            'tokens' => [
                '{id}' => '<id:\d+>'
            ],
            'extraPatterns' => [
                'OPTIONS {id}' => 'options',
                'POST login' => 'login',
                'OPTIONS login' => 'options',
                'POST change-password' => 'change-password',
                'OPTIONS change-password' => 'options',
                'POST password-reset-request' => 'password-reset-request',
                'OPTIONS password-reset-request' => 'options',
                'POST password-reset-token-verification' => 'password-reset-token-verification',
                'OPTIONS password-reset-token-verification' => 'options',
                'POST password-reset' => 'password-reset',
                'OPTIONS password-reset' => 'options'
            ]
        ],
   ]
];

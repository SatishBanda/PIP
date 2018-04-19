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
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/candidate',
            'pluralize' => false,
            'tokens' => [
                '{id}' => '<id:\d+>'
            ],
            'extraPatterns' => [
                'OPTIONS {id}' => 'options',
                'POST create-candidate' => 'create-candidate',
                'OPTIONS create-candidate' => 'options',
                'GET get-candidates-list' => 'get-candidates-list',
                'OPTIONS get-candidates-list' => 'options',
                'DELETE delete-candidate/{id}' => 'delete-candidate',
                'OPTIONS delete-candidate/{id}' => 'options',
                'PUT change-candidate-status/{id}' => 'change-candidate-status',
                'OPTIONS change-candidate-status/{id}' => 'options',
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/settings',
            'pluralize' => false,
            'tokens' => [
                '{id}' => '<id:\d+>'
            ],
            'extraPatterns' => [
                'OPTIONS {id}' => 'options',
                'GET get-settings' => 'get-settings',
                'OPTIONS get-settings' => 'options',
                'POST save-settings' => 'save-settings',
                'OPTIONS save-settings' => 'options',
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/evaluation',
            'pluralize' => false,
            'tokens' => [
                '{id}' => '<id:\d+>'
            ],
            'extraPatterns' => [
                'OPTIONS {id}' => 'options',
                'POST get-candidate-evaluation' => 'get-candidate-evaluation',
                'OPTIONS get-candidate-evaluation' => 'options',
                'POST save-evaluations' => 'save-evaluations',
                'OPTIONS save-evaluations' => 'options',
            ]
        ],
    ]
];

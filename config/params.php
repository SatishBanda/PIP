<?php

return [
    'supportEmail' => 'admin@acareportingservice.com',
    'adminEmail' => 'admin@pip.com',
    'jwtSecretCode' => 'someSecretKey',
    'user.passwordResetTokenExpire' => 86400, //currently 1 day. default is one hour i.e., 3600
    'jwt.access_token_expired_at' => 86400, //default is one day i.e., 86400
    'frontendURL' => 'http://18.236.68.236:4200/',
    'adminUserPermissions' => [
        1 => "Financials",
        2 => "Master Data",
        3 => "System Admin"
    ],
    'routePermissions' => [
        1 => [],
        2 => [
            '/v1/brands',
            '/v1/products',
            '/v1/lookup-options',
            '/v1/element-master',
            '/v1/manage-code',
            '/v1/email-templates'
        ],
        3 => [
            '/v1/error-log'
        ]
    ],
];

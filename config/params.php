<?php

return [
   // 'frontendURL'   => 'http://localhost:4201/',
	'ftpUserName'=>'HallCompany_SkyInsurance_TestFTP',
	'ftpUserPassword'=>'9Xq3JzYN',
	'ftpServer'=>'ftp1.carolina.sourcelink.com',
    'supportEmail'  =>  'admin@acareportingservice.com',
    'requestFormMail'  =>  'admin@acareportingservice.com',
	'supportPhone'  =>  '89998989898',
    'supportLogo'  =>  'ACA-Reporting-Logo.png', 
    'adminEmail'    => 'admin@acareportingservice.com',
    'jwtSecretCode' =>  'someSecretKey',
    'user.passwordResetTokenExpire' => 86400, //currently 1 day. default is one hour i.e., 3600
    'jwt.access_token_expired_at' => 86400, //default is one day i.e., 86400
    'frontendURL'=>'http://198.90.22.116:4200/',
    //'frontendURL'=>'http://localhost:4200/', //localhost is http://127.0.0.1:4200/
    'products' => ['ACA Reporting','Vht'],
    'adminUserPermissions' => [
        1 => "Financials",
        2 => "Master Data",
        3 => "System Admin"
    ],
    'shareFileDetails' => [
        'hostName' => "acareportingservice.sharefile.com",//acareportingservice.sharefile.com
        'userName' => "sampath@skyinsurancetech.com",//sampath@skyinsurancetech.com
        'password' => "SkyInsTech2016#",//SkyInsTech2016#
        'clientApiId' => "fBSwWRhtQxraa3KZwIjdjtl5WI2DF0he",//fBSwWRhtQxraa3KZwIjdjtl5WI2DF0he
        'clientSecretId'=>"PYVsHJqo62VsiSlGKG4K7pPBA41QMgGX8HftRljCtgv7HV2l"//PYVsHJqo62VsiSlGKG4K7pPBA41QMgGX8HftRljCtgv7HV2l
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

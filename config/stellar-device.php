<?php
return [
    'base_url' => env('STELLAR_DEVICE_API_BASE_URL', 'https://stellardevicesapiprod.azurewebsites.net/api/'),
    'username' => env('APPSETTING_API_USERNAME_STELLAR_DEVICE_API'),
    'password' => env('APPSETTING_API_PASSWORD_STELLAR_DEVICE_API'),
    'timeout' => env('STELLAR_DEVICE_API_TIMEOUT', 10),
];

<?php
return [
    'base_url' => env('STELLAR_DEVICE_API_BASE_URL', 'https://stellardevicesapiprod.azurewebsites.net/api/'),
    'username' => env('APPSETTING_API_USERNAME_STELLAR_DEVICE_API'),
    'password' => env('APPSETTING_API_PASSWORD_STELLAR_DEVICE_API'),
    'timeout' => env('STELLAR_DEVICE_API_TIMEOUT', 10),

    // Optional: Provide your own pool of random device names.
    // If both are provided, `names` takes precedence.
    //
    // `names` should be an array of strings.
    // `names_path` should be an absolute path to a JSON file containing either:
    //   - an array of strings, or
    //   - an object with a `names` array.
    'names' => [],
    'names_path' => env('STELLAR_DEVICE_NAMES_PATH'),
];

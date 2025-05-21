<?php
return [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'u68596',
        'user' => 'u68596',
        'pass' => '2859691'
    ],
    'security' => [
        'password_algo' => PASSWORD_DEFAULT,
        'cookie_options' => [
            'lifetime' => 30 * 24 * 60 * 60,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    ]
];
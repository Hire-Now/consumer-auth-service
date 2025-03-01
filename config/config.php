<?php

return [
    'app'   => [
        'url'           => getenv('APP_URL') ?: 'http://localhost',
        'consumer_auth' => [
            'jwt_validity_time'      => getenv('JWT_VALIDITY_TIME') ?: 1,
            'jwt_type_time'          => getenv('JWT_TYPE_TIME') ?: 'hours',
            'private_key_passphrase' => getenv('PRIVATE_KEY_PASSPHRASE') ?: '',
            'private_key_path'       => getenv('PRIVATE_AUTH_KEY_PATH') ?: '',
            'public_key_path'        => getenv('PUBLIC_AUTH_KEY_PATH') ?: '',
        ],
    ],
    'db'    => [
        'host'     => getenv('DATABASE_HOST') ?: '',
        'port'     => getenv('DATABASE_PORT') ?: '3307',
        'dbname'   => getenv('DATABASE_NAME') ?: '',
        'user'     => getenv('DATABASE_USER') ?: '',
        'password' => getenv('DATABASE_PASSWORD') ?: '',
        'charset'  => getenv('DATABASE_CHARSET') ?: '',
    ],
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => getenv('REDIS_PORT') ?: '6379',
    ]
];

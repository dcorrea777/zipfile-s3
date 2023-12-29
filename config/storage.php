<?php

declare(strict_types=1);

use function DI\env;

return [
    'bucket'        => env('STORAGE_NAME'),
    'default'       => env('STORAGE_DRIVE', 'local'),
    'connections'   => [
        's3' => [
                'region'    => env('AWS_REGION', 'us-east-1'),
                'version'   => 'latest',
        ],
        'local' => [
            'endpoint'                  => env('STORAGE_ENDPOINT'),
            'region'                    => env('AWS_REGION'),
            'use_path_style_endpoint'   => true,
            'version'                   => 'latest',
            'credentials'               => ['key' => env('STORAGE_ACCESS_KEY'), 'secret' => env('STORAGE_SECRET_KEY')],
        ],
    ],
];

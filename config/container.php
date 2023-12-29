<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Psr\Container\ContainerInterface;

return [
    S3ClientInterface::class => function (ContainerInterface $container): S3ClientInterface {
        $config = $container->get('app');
        $driver = $config['default'];

        return new S3Client($config['connections'][$driver]);
    },
    'files' => [
        'file-0.txt',
        'file-1.txt',
        'file-2.txt',
        'file-3.txt',
        'file-4.txt',
        'file-5.txt',
        'file-6.txt',
        'file-7.txt',
        'file-8.txt',
        'file-9.txt',
    ]
];

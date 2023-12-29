<?php

declare(strict_types=1);

use Aws\CommandInterface;
use Aws\CommandPool;
use Aws\ResultInterface;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use ZipStream\ZipStream;

try {
    $container      = require_once __DIR__ . '/config/bootstrap.php';
    $storage        = $container->get(S3ClientInterface::class);
    $objectsToZip   = $container->get('files');
    $bucket         = $container->get('app')['bucket'];

    $storage->registerStreamWrapper();
    $zipStream = fopen("s3://$bucket/example.zip", 'w');

    $zip = new ZipStream(
        enableZip64: false,
        outputStream: $zipStream,
        sendHttpHeaders: true,
    );

    $fulfilled = function (ResultInterface $result, $item, PromiseInterface $aggregatePromise,) use ($zip, $objectsToZip) {
        dump("Downloaded file: " . $objectsToZip[$item]);
        $zip->addFileFromPsr7Stream(
            fileName: $objectsToZip[$item],
            stream: $result->get('Body'),
        );
        dump("Added in zip: " . $objectsToZip[$item]);
    };

    $before = function (CommandInterface $cmd, $iterKey) use ($objectsToZip) {
        dump('Starting download: ' . $cmd->toArray()['Key']);
    };

    $commands = [];
    foreach ($objectsToZip as $objectKey) {
        $commands[] = $storage->getCommand('getObject', [
            'Bucket'    => 'zip',
            'Key'       => $objectKey,
        ]);
    }

    // Create a command pool
    $pool = new CommandPool($storage, $commands, ['fulfilled' => $fulfilled, 'before' => $before]);
    $promise = $pool->promise();
    $promise->wait();
    $zip->finish();

    fclose($zipStream);
} catch (Throwable $e) {
    dump($e->getMessage());
}



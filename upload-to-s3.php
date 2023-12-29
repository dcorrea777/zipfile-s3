<?php

declare(strict_types=1);

use Aws\CommandInterface;
use Aws\CommandPool;
use Aws\ResultInterface;
use Aws\S3\S3ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;

$container  = require_once __DIR__ . '/config/bootstrap.php';
$storage    = $container->get(S3ClientInterface::class);
$files      = $container->get('files');
$bucket     = $container->get('app')['bucket'];

$buckets = $storage->listBuckets()->get('Buckets');
$buckets = array_column($buckets, 'Name');

if (!in_array('zip', $buckets)) {
    echo 'Creating bucket zip', PHP_EOL;
    $storage->createBucket(['Bucket' => 'zip']);
};

$fallocate = function (string $fileName, string $size = '1MB'): array {
    $path       = __DIR__ . '/storage';
    $statusCode = null;
    $output     = [];
    $command    = sprintf('fallocate -l %s %s/%s', $size, $path, $fileName);

    exec($command, $output, $statusCode);

    return ['output' => $output, 'statusCode' => $statusCode];
};

$fulfilled = function (ResultInterface $result, $item, PromiseInterface $aggregatePromise) {
    echo 'Uploaded file: ', $result['@metadata']['effectiveUri'], PHP_EOL;
};

$before = function (CommandInterface $cmd, $iterKey) use ($files) {
    echo 'Starting to upload: ', $files[$iterKey], PHP_EOL;
};

$commands = [];
foreach($files as $file) {
    $fallocate($file);
    $commands[] = $storage->getCommand('putObject', [
        'Bucket'        => $bucket,
        'Key'           => $file,
        'SourceFile'    => __DIR__ . '/storage/' . $file,
    ]);
}

$pool = new CommandPool($storage, $commands, ['fulfilled' => $fulfilled, 'before' => $before]);
$promise = $pool->promise();
$promise->wait();

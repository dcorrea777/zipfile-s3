<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$pathStorage    = __DIR__ . '/storage.php';
$pathContainer  = __DIR__ . '/container.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(require $pathContainer);
$containerBuilder->addDefinitions(['app' => require $pathStorage]);

return $containerBuilder->build();

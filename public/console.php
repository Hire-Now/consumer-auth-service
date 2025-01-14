<?php

$bootstrap = require_once __DIR__ . '/../public/base_initialization.php';
$container = $bootstrap['container'];

use App\Infrastructure\Console\CreateApiConsumerCommand;
use Symfony\Component\Console\Application;

$console = new Application();
$console->add($container->get(CreateApiConsumerCommand::class));
$console->run();
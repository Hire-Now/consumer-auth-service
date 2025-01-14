<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Infrastructure\Providers\ServiceProvider;
use App\Infrastructure\Console\CreateApiConsumerCommand;
use DI\Container;
use Symfony\Component\Console\Application;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$config = require_once __DIR__ . '/../config/config.php';

$container = new Container();

AppFactory::setContainer($container);

$serviceProvider = new ServiceProvider();
$serviceProvider($container);

$app = AppFactory::create();

$container->set('config', function () use ($config) {
    return $config;
});

$console = new Application();
$console->add($container->get(CreateApiConsumerCommand::class));
$console->run();

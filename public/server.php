<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Adapters\Inbound\ConsumerController;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Infrastructure\Providers\ServiceProvider;
use DI\Container;

error_reporting(E_ERROR | E_WARNING);

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

$app->get('/health', function ($request, $response) {
    $response->getBody()->write('Service is running');
    return $response;
});

$app->post('/authenticate', function ($request, $response, $args) use ($container) {
    $consumerController = $container->get(ConsumerController::class);
    return $consumerController->authenticatePost($request, $response, $args);
});

$app->run();
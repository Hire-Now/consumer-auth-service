<?php

use App\Infrastructure\Adapters\Inbound\ConsumerController;
use App\Infrastructure\Router\RedisRouter;

/** @var \DI\Container $container */
$router = new RedisRouter($container);

$router->addRoute('auth_channel', 'authenticate', [ ConsumerController::class, 'authenticate' ]);

return $router;
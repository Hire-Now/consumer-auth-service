<?php

use App\Infrastructure\Adapters\Inbound\ConsumerController;
use App\Infrastructure\Router\RedisRouter;

/** @var \DI\Container $container */
$router = new RedisRouter($container);

$router->addRoute('auth_consumer_channel', 'authenticate', [ ConsumerController::class, 'authenticate' ]);
$router->addRoute('auth_consumer_channel', 'check-token', [ ConsumerController::class, 'validateToken' ]);

return $router;
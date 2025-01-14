<?php

use Slim\Routing\RouteCollectorProxy;
use App\Infrastructure\Adapters\Inbound\ConsumerController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/** @var \Slim\App $app */
/** @var \DI\Container $container */

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->addBodyParsingMiddleware();

$app->get('/health', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'status'    => 'ok',
        'timestamp' => date('Y-m-d H:i:s')
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->group('/api', function (RouteCollectorProxy $group) use ($container) {
    $group->post('/authenticate', function (Request $request, Response $response, array $args) use ($container) {
        return $container->get(ConsumerController::class)->authenticatePost($request, $response, $args);
    });
});
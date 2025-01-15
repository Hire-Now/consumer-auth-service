<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Dotenv\Dotenv;
use App\Infrastructure\Providers\ServiceProvider;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Psr7Response;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $config = require_once __DIR__ . '/../config/config.php';

    $container = new Container();
    $container->set('config', $config);

    $serviceProvider = new ServiceProvider();
    $serviceProvider($container);

    AppFactory::setContainer($container);
    $app = AppFactory::create();

    $app->addBodyParsingMiddleware();

    $app->add(function (Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Content-Type', 'application/json');
    });

    $errorMiddleware = $app->addErrorMiddleware(
        $_ENV['APP_DEBUG'] ?? false,
        true,
        true
    );

    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->forceContentType('application/json');

    $customErrorHandler = function (Request $request, \Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {
        $payload = [
            'status'  => 'error',
            'message' => $exception->getMessage()
        ];

        if ($displayErrorDetails) {
            $payload['trace'] = $exception->getTraceAsString();
            $payload['file'] = $exception->getFile();
            $payload['line'] = $exception->getLine();
        }

        $response = new Psr7Response();
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $statusCode = 500;
        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
        }

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    };

    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);

    return [
        'container' => $container,
        'app'       => $app
    ];

} catch (\Throwable $e) {
    $payload = [
        'status'  => 'error',
        'message' => 'Error de inicialización: ' . $e->getMessage(),
        'trace'   => $_ENV['APP_DEBUG'] ?? false ? $e->getTraceAsString() : null
    ];

    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit(1);
}
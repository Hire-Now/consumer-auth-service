<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Adapters\Inbound\ConsumerController;
use App\Infrastructure\Providers\ServiceProvider;
use Dotenv\Dotenv;
use DI\Container;

error_reporting(E_ERROR | E_WARNING);

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$config = require_once __DIR__ . '/../config/config.php';

$container = new Container();

$serviceProvider = new ServiceProvider();
$serviceProvider($container);

$redis = new \Predis\Client([
    'scheme' => 'tcp',
    'host'   => $config['redis']['host'],
    'port'   => $config['redis']['port'],
]);

$container->set('config', function () use ($config) {
    return $config;
});
$container->set('redis', $redis);

$consumerController = $container->get(ConsumerController::class);

try {
    $redis->subscribe([ 'auth_channel' ], function ($message) use ($consumerController) {
        $data = json_decode($message, true);

        if ($data === null || !isset($data['action'])) {
            echo "Mensaje mal formado recibido: $message" . PHP_EOL;
            return;
        }

        if ($data['action'] === 'authenticate') {
            $response = $consumerController->authenticate($data);
            echo "Resultado de autenticación: " . json_encode($response) . PHP_EOL;
        }
    });
} catch (\Exception $e) {
    echo "Error en el listener de Redis: " . $e->getMessage() . PHP_EOL;
}
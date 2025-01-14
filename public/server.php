<?php

try {
    $bootstrap = require_once __DIR__ . '/../public/base_initialization.php';
    $app = $bootstrap['app'];

    require_once __DIR__ . '/../src/Infrastructure/Routes/api.php';

    $app->run();

} catch (\Throwable $e) {
    $payload = [
        'status'  => 'error',
        'message' => 'Error fatal del servidor: ' . $e->getMessage(),
        'trace'   => $_ENV['APP_DEBUG'] ?? false ? $e->getTraceAsString() : null
    ];

    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit(1);
}
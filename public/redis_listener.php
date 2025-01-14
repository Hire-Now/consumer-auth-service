<?php

$bootstrap = require_once __DIR__ . '/../public/base_initialization.php';
$container = $bootstrap['container'];

use Predis\Client;

const DEBUG_LEVEL = 2;
const LOG_FILE = __DIR__ . '/../src/Infrastructure/storage/logs/redis.log';

function debug($message, $level = 1, $data = null)
{
    if ($level > DEBUG_LEVEL)
        return;

    $datetime = date('Y-m-d H:i:s');
    $formattedMessage = "[$datetime] $message";

    if ($data !== null) {
        $formattedData = print_r($data, true);
        $formattedMessage .= "\nData: $formattedData\n";
    }

    echo $formattedMessage . PHP_EOL;

    file_put_contents(LOG_FILE, $formattedMessage . PHP_EOL, FILE_APPEND);
}

try {
    debug("Iniciando Redis listener...", 1);

    // Verificar configuración
    debug("Configuración Redis:", 2, [
        'host' => $config['redis']['host'],
        'port' => $config['redis']['port']
    ]);

    $redis = new Client([
        'scheme'             => 'tcp',
        'host'               => $config['redis']['host'],
        'port'               => $config['redis']['port'],
        'read_write_timeout' => 0,
    ]);

    try {
        $redis->ping();
        debug("Conexión Redis establecida exitosamente", 1);
    } catch (\Exception $e) {
        debug("Error en conexión Redis: " . $e->getMessage(), 1);
        throw $e;
    }

    $container->set('redis', $redis);

    debug("Cargando router...", 2);
    $router = require_once __DIR__ . '/../src/Infrastructure/Routes/redis.php';

    $channels = $router->getChannels();
    debug("Canales disponibles:", 2, $channels);

    $pubsub = $redis->pubSubLoop();

    foreach ($channels as $channel) {
        $pubsub->subscribe($channel);
        debug("Suscrito al canal: $channel", 1);
    }

    debug("Iniciando loop de mensajes...", 1);

    /** @var stdClass $message */
    foreach ($pubsub as $message) {
        debug("Mensaje recibido:", 2, [
            'tipo'    => $message->kind ?? 'desconocido',
            'canal'   => $message->channel ?? 'sin canal',
            'payload' => $message->payload ?? 'sin payload'
        ]);

        if ($message->kind === 'message') {
            try {
                $data = json_decode($message->payload, true);
                debug("Payload decodificado:", 2, $data);

                if ($data === null) {
                    debug("Error: JSON mal formado", 1, $message->payload);
                    throw new \InvalidArgumentException("Mensaje JSON mal formado");
                }

                if (!isset($data['action'])) {
                    debug("Error: Falta action", 1, $data);
                    throw new \InvalidArgumentException("Campo 'action' faltante");
                }

                debug("Procesando acción: " . $data['action'], 1);
                $response = $router->handle($message->channel, $data['action'], $data);
                debug("Respuesta:", 2, $response);

                echo "[" . date('Y-m-d H:i:s') . "] Canal: {$message->channel}, Acción: {$data['action']}, Respuesta: " . PHP_EOL .
                    json_encode($response, JSON_UNESCAPED_UNICODE) . PHP_EOL;

            } catch (\Exception $e) {
                debug("Error procesando mensaje: " . $e->getMessage(), 1, [
                    'canal' => $message->channel,
                    'stack' => $e->getTraceAsString()
                ]);
            }
        }
    }

} catch (\Exception $e) {
    debug("Error fatal: " . $e->getMessage(), 1, [
        'stack' => $e->getTraceAsString()
    ]);
    exit(1);
}
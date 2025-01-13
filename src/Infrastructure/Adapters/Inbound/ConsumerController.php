<?php

namespace App\Infrastructure\Adapters\Inbound;

use App\Application\Commands\AuthenticateConsumerCommand;
use App\Application\Commands\GenerateJWTForConsumerCommand;
use App\Application\Handlers\AuthenticateConsumerCommandHandler;
use App\Application\Handlers\GenerateJWTForConsumerCommandHandler;
use Monolog\Logger;

class ConsumerController
{
    private $config;
    private $logger;

    public function __construct(
        private readonly AuthenticateConsumerCommandHandler $authenticateConsumerCommandHandler,
        private readonly GenerateJWTForConsumerCommandHandler $generateJWTForConsumerCommandHandler,
        $config,
        Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function authenticate($request, $response, $args)
    {
        try {
            $authorization = $request->getHeaderLine('Authorization');

            if (!$authorization) {
                $this->logger->warning('Authorization header is missing.');
                throw new \Exception('Authorization header is missing.');
            }

            $authenticateConsumerCommand = new AuthenticateConsumerCommand($authorization);
            $consumer = $this->authenticateConsumerCommandHandler->handle($authenticateConsumerCommand);

            $generateJWTForConsumerCommand = new GenerateJWTForConsumerCommand($consumer);
            $jwt = $this->generateJWTForConsumerCommandHandler->handle($generateJWTForConsumerCommand);

            $expiresIn = $this->config['app']['consumer_auth']['jwt_validity_time'] . ' ' . $this->config['app']['consumer_auth']['jwt_type_time'];


            $response->getBody()->write(json_encode([
                'status'  => 'SUCCESS',
                'message' => 'Authentication successful',
                'data'    => [
                    'token'      => $jwt,
                    'type'       => 'Bearer',
                    'expires_in' => $expiresIn,
                ]
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $th) {
            $this->logger->error("Error in ConsumerController@authenticate: {$th->getMessage()}", [
                'trace' => $th->getTraceAsString()
            ]);

            $response->getBody()->write(json_encode([
                'status'  => 'ERROR',
                'message' => 'Failed to authenticate the consumer, please try again later.',
                'data'    => []
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}


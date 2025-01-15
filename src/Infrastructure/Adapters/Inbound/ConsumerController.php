<?php

namespace App\Infrastructure\Adapters\Inbound;

use App\Application\Commands\AuthenticateConsumerCommand;
use App\Application\Commands\GenerateJWTForConsumerCommand;
use App\Application\Commands\ValidateJWTCommand;
use App\Application\Exceptions\HttpUnauthorizedException;
use App\Application\Handlers\AuthenticateConsumerCommandHandler;
use App\Application\Handlers\GenerateJWTForConsumerCommandHandler;
use App\Application\Handlers\ValidateJWTCommandHandler;
use Monolog\Logger;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ConsumerController
{
    private string $expiresIn = '';

    public function __construct(
        private readonly AuthenticateConsumerCommandHandler $authenticateConsumerCommandHandler,
        private readonly GenerateJWTForConsumerCommandHandler $generateJWTForConsumerCommandHandler,
        private readonly ValidateJWTCommandHandler $validateJWTCommandHandler,
        private array $config,
        private Logger $logger
    ) {
        $this->expiresIn = $this->config['app']['consumer_auth']['jwt_validity_time'] . ' ' . $this->config['app']['consumer_auth']['jwt_type_time'];
    }

    public function authenticate($data)
    {
        try {
            $authorization = $data['authorization'] ?? null;

            if (!$authorization) {
                throw new \Exception('Authorization header is missing.');
            }

            $authenticateConsumerCommand = new AuthenticateConsumerCommand($authorization);
            $consumer = $this->authenticateConsumerCommandHandler->handle($authenticateConsumerCommand);

            $generateJWTForConsumerCommand = new GenerateJWTForConsumerCommand($consumer);
            $jwt = $this->generateJWTForConsumerCommandHandler->handle($generateJWTForConsumerCommand);

            return [
                'status'  => 'SUCCESS',
                'message' => 'Authentication successful',
                'data'    => [
                    'token'      => $jwt,
                    'type'       => 'Bearer',
                    'expires_in' => $this->expiresIn,
                ],
            ];
        } catch (HttpUnauthorizedException | NotFoundResourceException $th) {
            $this->logger->warning("Warning in ConsumerController@validateToken: {$th->getMessage()}", [
                'trace'     => $th->getTraceAsString(),
                'previous'  => $th->getPrevious(),
                'exception' => get_class($th)
            ]);

            return [
                'status'  => 'ERROR',
                'message' => 'Provided credentials are invalid, please check your credentials and try again.',
                'data'    => [],
            ];
        } catch (\Throwable $th) {
            $this->logger->error("Error in ConsumerController@authenticate: {$th->getMessage()}", [
                'trace'    => $th->getTraceAsString(),
                'previous' => $th->getPrevious()
            ]);

            return [
                'status'  => 'ERROR',
                'message' => 'Failed to authenticate the consumer, please try again later.',
                'data'    => [],
            ];
        }
    }

    public function validateToken($data)
    {
        try {
            $authorization = $data['consumer-authorization'] ?? null;

            if (!$authorization) {
                throw new \Exception('Consumer authorization is missing.');
            }

            $validateJWTCommand = new ValidateJWTCommand($authorization);
            $consumer = $this->validateJWTCommandHandler->handle($validateJWTCommand);

            return [
                'status'  => 'SUCCESS',
                'message' => 'Token validated succesfully',
                'data'    => [
                    'consumer' => $consumer->toArray()
                ],
            ];
        } catch (HttpUnauthorizedException | NotFoundResourceException $th) {
            $this->logger->warning("Warning in ConsumerController@validateToken: {$th->getMessage()}", [
                'trace'     => $th->getTraceAsString(),
                'previous'  => $th->getPrevious(),
                'exception' => get_class($th)
            ]);

            return [
                'status'  => 'ERROR',
                'message' => 'Provided token is invalid, please check it and try again.',
                'data'    => [],
            ];
        } catch (\Throwable $th) {
            $this->logger->error("Error in ConsumerController@validateToken: {$th->getMessage()}", [
                'trace'    => $th->getTraceAsString(),
                'previous' => $th->getPrevious()
            ]);

            return [
                'status'  => 'ERROR',
                'message' => 'Failed to validate the token, please try again later.',
                'data'    => [],
            ];
        }
    }
}


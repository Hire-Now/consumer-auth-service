<?php

namespace App\Infrastructure\Adapters\Inbound;

use App\Application\Commands\AuthenticateConsumerCommand;
use App\Application\Commands\GenerateJWTForConsumerCommand;
use App\Application\Exceptions\HttpUnauthorizedException;
use App\Application\Handlers\AuthenticateConsumerCommandHandler;
use App\Application\Handlers\GenerateJWTForConsumerCommandHandler;
use Monolog\Logger;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ConsumerController
{

    public function __construct(
        private readonly AuthenticateConsumerCommandHandler $authenticateConsumerCommandHandler,
        private readonly GenerateJWTForConsumerCommandHandler $generateJWTForConsumerCommandHandler,
        private array $config,
        private Logger $logger
    ) {
    }

    public function authenticatePost($request, $response, $args)
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
        } catch (HttpUnauthorizedException | NotFoundResourceException $th) {
            $response->getBody()->write(json_encode([
                'status'  => 'ERROR',
                'message' => 'Provided credentials are invalid, please check your credentials and try again.',
                'data'    => []
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } catch (\Throwable $th) {
            $this->logger->error("Error in ConsumerController@authenticate: {$th->getMessage()}", [
                'trace'    => $th->getTraceAsString(),
                'previous' => $th->getPrevious()
            ]);

            $response->getBody()->write(json_encode([
                'status'  => 'ERROR',
                'message' => 'Failed to authenticate the consumer, please try again later.',
                'data'    => []
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
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

            $expiresIn = $this->config['app']['consumer_auth']['jwt_validity_time'] . ' ' . $this->config['app']['consumer_auth']['jwt_type_time'];

            return [
                'status'  => 'SUCCESS',
                'message' => 'Authentication successful',
                'data'    => [
                    'token'      => $jwt,
                    'type'       => 'Bearer',
                    'expires_in' => $expiresIn,
                ],
            ];
        } catch (HttpUnauthorizedException | NotFoundResourceException $th) {
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
}


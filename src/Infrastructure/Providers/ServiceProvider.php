<?php

namespace App\Infrastructure\Providers;

use Monolog\Level;
use Psr\Container\ContainerInterface;
use App\Application\Handlers\AuthenticateConsumerCommandHandler;
use App\Application\Handlers\GenerateJWTForConsumerCommandHandler;
use App\Infrastructure\Adapters\Inbound\ConsumerController;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Application\UseCases\AuthUseCase;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use App\Domain\Ports\Outbound\JWTTokenRepositoryPort;
use App\Infrastructure\Database\DBConnection;
use App\Infrastructure\Persistence\ConsumerRepository;
use App\Infrastructure\Persistence\JWTTokenRepository;
use App\Application\Services\JWTAuthService;
use App\Infrastructure\Console\CreateApiConsumerCommand;
use DI\Container;

class ServiceProvider
{
    public function __invoke(Container $container)
    {
        $container->set('db', function (ContainerInterface $container) {
            $pdo = new DBConnection($container->get('config')['db']);
            return $pdo->getPdo();
        });

        $container->set(ConsumerRepositoryPort::class, function (ContainerInterface $container) {
            $pdo = $container->get('db');
            return new ConsumerRepository($pdo);
        });

        $container->set(JWTTokenRepositoryPort::class, function (ContainerInterface $container) {
            $pdo = $container->get('db');
            return new JWTTokenRepository($pdo);
        });

        $container->set('logger', function () {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../../Infrastructure/storage/logs/app.log', Level::Debug));
            return $logger;
        });

        $container->set(JWTAuthService::class, function (Container $container) {
            return new JWTAuthService($container->get('config'));
        });

        $container->set(AuthUseCase::class, function (Container $container) {
            return new AuthUseCase(
                $container->get(JWTAuthService::class),
                $container->get(ConsumerRepositoryPort::class),
                $container->get(JWTTokenRepositoryPort::class)
            );
        });

        $container->set(ConsumerController::class, function (Container $container) {
            return new ConsumerController(
                $container->get(AuthenticateConsumerCommandHandler::class),
                $container->get(GenerateJWTForConsumerCommandHandler::class),
                $container->get('config'),
                $container->get('logger')
            );
        });

        $container->set(AuthenticateConsumerCommandHandler::class, function (Container $container) {
            return new AuthenticateConsumerCommandHandler(
                $container->get(AuthUseCase::class)
            );
        });

        $container->set(GenerateJWTForConsumerCommandHandler::class, function (Container $container) {
            return new GenerateJWTForConsumerCommandHandler(
                $container->get(AuthUseCase::class)
            );
        });

        $container->set(CreateApiConsumerCommand::class, function (Container $container) {
            return new CreateApiConsumerCommand(
                $container->get(ConsumerRepositoryPort::class)
            );
        });
    }
}

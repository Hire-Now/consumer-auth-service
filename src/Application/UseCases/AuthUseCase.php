<?php

namespace App\Application\UseCases;

use App\Application\Ports\Inbound\IAuthPort;
use App\Domain\Entities\Consumer;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use App\Domain\Ports\Outbound\JWTTokenRepositoryPort;
use App\Application\Services\JWTAuthService;

class AuthUseCase implements IAuthPort
{
    public function __construct(
        private readonly JWTAuthService $authService,
        private readonly ConsumerRepositoryPort $consumerRepository,
        private readonly JWTTokenRepositoryPort $repository
    ) {
    }

    public function authenticate(string $authorizationHeader): ?Consumer
    {
        try {
            if (!str_starts_with($authorizationHeader, 'Basic ')) {
                return null;
            }

            [ $clientId, $clientSecret ] = $this->authService->extractCliendIDClientSecret($authorizationHeader);

            $consumer = $this->consumerRepository->findByClientId($clientId);

            $isValidConsumer = $this->authService->consumerCredentialsAreValid($clientSecret, $consumer);

            if (!$isValidConsumer) {
                // throw new HttpUnauthorizedException();
            }

            $this->consumerRepository->updateLastAccess($consumer);

            return $consumer;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }

    public function generateJWT(Consumer $consumer): string
    {
        try {
            $jwtEntity = $this->authService->generateJWT($consumer);

            $this->repository->create($jwtEntity);

            return $jwtEntity->getJwt();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }

    public function validateJWT(string $token): bool
    {
        return $this->authService->validateJWT($token);
    }
}

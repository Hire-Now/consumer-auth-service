<?php

namespace App\Application\UseCases;

use App\Application\Exceptions\HttpUnauthorizedException;
use App\Application\Ports\Inbound\IAuthPort;
use App\Domain\Entities\Consumer;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use App\Domain\Ports\Outbound\JWTTokenRepositoryPort;
use App\Application\Services\JWTAuthService;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AuthUseCase implements IAuthPort
{
    public function __construct(
        private readonly JWTAuthService $authService,
        private readonly ConsumerRepositoryPort $consumerRepository,
        private readonly JWTTokenRepositoryPort $jwtRepository
    ) {
    }

    public function authenticate(string $authorizationHeader): ?Consumer
    {
        try {
            if (!str_starts_with($authorizationHeader, 'Basic ')) {
                throw new HttpUnauthorizedException("Invalid credentials structure, please check them and try it again.");
            }

            [ $clientId, $clientSecret ] = $this->authService->extractCliendIDClientSecret($authorizationHeader);

            $consumer = $this->consumerRepository->findConsumerByClientIdOrFail($clientId);

            $isValidConsumer = $this->authService->consumerCredentialsAreValid($clientSecret, $consumer);

            if (!$isValidConsumer) {
                throw new HttpUnauthorizedException("Invalid credentials, please check them and try it again.");
            }

            $this->consumerRepository->updateLastAccess($consumer);

            return $consumer;
        } catch (NotFoundResourceException $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }

    public function generateJWT(Consumer $consumer): string
    {
        try {
            $jwtEntity = $this->authService->generateJWT($consumer);

            $this->jwtRepository->create($jwtEntity);

            return $jwtEntity->getJwt();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }

    public function validateJWT(string $token): Consumer
    {
        try {
            if (!str_starts_with($token, 'Bearer ')) {
                throw new HttpUnauthorizedException("Invalid credentials structure, please check them and try it again.");
            }

            $tokenIsValid = $this->authService->validateJWT($token);

            if (!$tokenIsValid) {
                throw new HttpUnauthorizedException('Token is invalid and thereforer it could not be processed.', 401);
            }

            $consumer = $this->consumerRepository->findConsumerByIdOrFail($tokenIsValid->sub);

            $this->jwtRepository->findJWTByJtiAndConsumerIdOrFail($tokenIsValid->jti, $consumer->getId());

            return $consumer;
        } catch (HttpUnauthorizedException $th) {
            throw $th;
        } catch (NotFoundResourceException $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }
}

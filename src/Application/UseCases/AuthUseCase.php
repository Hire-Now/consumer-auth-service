<?php

namespace App\Application\UseCases;

use App\Application\Contracts\AuthInterface;
use App\Application\Ports\Inbound\IAuthPort;

class AuthUseCase
{
    public function __construct(private readonly IAuthPort $authService)
    {
    }

    public function generateJWT(string $username, string $password): string
    {
        return $this->authService->generateJWT($username, $password);
    }

    public function validateJWT(string $token): bool
    {
        return $this->authService->validateJWT($token);
    }
}

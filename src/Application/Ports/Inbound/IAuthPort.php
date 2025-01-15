<?php

namespace App\Application\Ports\Inbound;

use App\Domain\Entities\Consumer;

interface IAuthPort
{
    public function authenticate(string $authorizationHeader): ?Consumer;
    public function generateJWT(Consumer $consumer): string;
    public function validateJWT(string $token): Consumer;
}
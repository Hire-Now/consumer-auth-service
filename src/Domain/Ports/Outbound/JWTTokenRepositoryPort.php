<?php

namespace App\Domain\Ports\Outbound;

use App\Domain\Entities\JwtToken;

interface JWTTokenRepositoryPort
{
    public function create(JwtToken $entity): JwtToken;
    public function findByJtiAndUserId(string $jti, string $userId, string $status = 'valid'): array;
}

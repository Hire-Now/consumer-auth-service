<?php

namespace App\Domain\Ports\Outbound;

use App\Domain\Entities\JwtToken;

interface JWTTokenRepositoryPort
{
    public function create(JwtToken $entity): JwtToken;
    public function findJWTByJtiAndConsumerIdOrFail(string $jti, string $consumerId, string $status = 'valid'): JWTToken;
}

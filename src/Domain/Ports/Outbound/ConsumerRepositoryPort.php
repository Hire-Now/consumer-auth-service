<?php

namespace App\Domain\Ports\Outbound;

use App\Domain\Entities\Consumer;

interface ConsumerRepositoryPort
{
    public function create(Consumer $consumer): Consumer;
    public function findConsumerByClientIdOrFail(string $clientId): ?Consumer;
    public function findConsumerByIdOrFail(string $id): ?Consumer;
    public function updateLastAccess(Consumer $consumer): void;
}

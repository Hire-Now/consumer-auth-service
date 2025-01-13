<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Ports\Outbound\JWTTokenRepositoryPort;
use App\Domain\Entities\JWTToken;
use Illuminate\Support\Str;
use PDO;

class JWTTokenRepository implements JWTTokenRepositoryPort
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(JwtToken $entity): JwtToken
    {
        $query = "INSERT INTO jwt_tokens (id, entity_id, jti, status, expiry_time, type_time, owner ) VALUES (:id :entity_id, :jti, 'valid', :expiry_time, :type_time, :owner)";

        $stmt = $this->pdo->prepare($query);

        $recordId = Str::uuid()->toString();

        $stmt->bindParam(':id', $recordId);
        $stmt->bindParam(':entity_id', $entity->getEntityId());
        $stmt->bindParam(':jti', $entity->getJti());
        $stmt->bindParam(':expiry_time', $entity->getExpiryTime());
        $stmt->bindParam(':type_time', $entity->getTypeTime());
        $stmt->bindParam(':owner', $entity->getOwner());

        $entity->setId($recordId);

        return $entity;
    }

    public function findByJtiAndUserId(string $jti, string $userId, string $status = 'valid'): array
    {
        return [];
    }
}

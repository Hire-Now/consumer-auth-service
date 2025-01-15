<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Ports\Outbound\JWTTokenRepositoryPort;
use App\Domain\Entities\JWTToken;
use Illuminate\Support\Str;
use PDO;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class JWTTokenRepository implements JWTTokenRepositoryPort
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(JwtToken $entity): JwtToken
    {
        try {
            $query = "INSERT INTO jwt_tokens (id, entity_id, jti, status, expiry_time, type_time, owner) VALUES (:id, :entity_id, :jti, :status, :expiry_time, :type_time, :owner)";

            $stmt = $this->pdo->prepare($query);

            $recordId = Str::uuid()->toString();
            $status = 'active';

            $stmt->bindParam(':id', $recordId);
            $stmt->bindParam(':entity_id', $entity->getEntityId());
            $stmt->bindParam(':jti', $entity->getJti());
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':expiry_time', $entity->getExpiryTime());
            $stmt->bindParam(':type_time', $entity->getTypeTime());
            $stmt->bindParam(':owner', $entity->getOwner());

            $stmt->execute();

            $entity->setId($recordId);

            return $entity;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }

    public function findJWTByJtiAndConsumerIdOrFail(string $jti, string $consumerId, string $status = 'active'): JWTToken
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM jwt_tokens WHERE entity_id = :entity_id AND jti = :jti AND status = :status');
            $stmt->execute([ 'entity_id' => $consumerId, 'jti' => $jti, 'status' => $status ]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new NotFoundResourceException('Resource not found, please try again.', 404);
            }

            return new JWTToken(
                $data['id'],
                $data['entity_id'],
                $data['jti'],
                $data['expiry_time'],
                $data['type_time'],
                $data['owner'],
                null
            );
        } catch (NotFoundResourceException $th) {
            throw new $th;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 0, $th);
        }
    }
}

<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Consumer;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use Carbon\Carbon;
use PDO;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Illuminate\Support\Str;

class ConsumerRepository implements ConsumerRepositoryPort
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(Consumer $entity): Consumer
    {
        $query = "INSERT INTO api_consumers ( id, name, client_id, client_secret, description,  is_active, last_activity, created_at, updated_at ) VALUES
              (:id, :name, :client_id, :client_secret, :description, :is_active, :last_activity, :created_at, :updated_at)";

        $stmt = $this->pdo->prepare($query);

        $recordId = Str::uuid()->toString();

        $name = $entity->getName();
        $clientId = $entity->getClientId();
        $clientSecret = $entity->getClientSecret();
        $description = $entity->getDescription();
        $isActive = $entity->getIsActive();
        $lastActivity = $entity->getLastActivity();
        $createdAt = $entity->getCreatedAt();
        $updatedAt = $entity->getUpdatedAt();

        $entity->setId($recordId);

        $stmt->bindParam(':id', $recordId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':client_id', $clientId);
        $stmt->bindParam(':client_secret', $clientSecret);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->bindParam(':last_activity', $lastActivity);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->bindParam(':updated_at', $updatedAt);

        $stmt->execute();

        return $entity;
    }

    public function findByClientId(string $clientId): ?Consumer
    {
        $stmt = $this->pdo->prepare('SELECT * FROM api_consumers WHERE client_id = :client_id');
        $stmt->execute([ 'client_id' => $clientId ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new NotFoundResourceException('Resource not found, please try again.', 404);
        }

        return new Consumer(
            $data['id'],
            $data['name'],
            $data['client_id'],
            $data['client_secret'],
            $data['description'],
            $data['is_active'],
            Carbon::parse($data['last_activity']),
            Carbon::parse($data['created_at']),
            Carbon::parse($data['updated_at']),
        );
    }

    public function updateLastAccess(Consumer $consumer): void
    {
        $stmt = $this->pdo->prepare('UPDATE api_consumers SET last_activity = NOW() WHERE client_id = :client_id');
        $stmt->execute([ 'client_id' => $consumer->getClientId() ]);
    }

}

<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Consumer;
use App\Domain\Ports\Outbound\ConsumerRepositoryPort;
use Carbon\Carbon;
use PDO;

class ConsumerRepository implements ConsumerRepositoryPort
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByClientId(string $clientId): ?Consumer
    {
        $stmt = $this->pdo->prepare('SELECT * FROM api_consumers WHERE client_id = :client_id');
        $stmt->execute([ 'client_id' => $clientId ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Consumer(
                $data['id'],
                $data['name'],
                $data['client_id'],
                $data['client_secret'],
                $data['description'],
                $data['is_active'],
                Carbon::parse($data['last_activity'])
            );
        }

        return null;
    }

    public function updateLastAccess(Consumer $consumer): void
    {
        $stmt = $this->pdo->prepare('UPDATE consumers SET last_access = NOW() WHERE client_id = :client_id');
        $stmt->execute([ 'client_id' => $consumer->getClientId() ]);
    }

}

<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOStatement;

class QueryLoggerPDO extends PDO
{
    private PDO $pdo;

    public function __construct(PDO $pdo, private mixed $logger)
    {
        $this->pdo = $pdo;
    }

    public function prepare($statement, $options = []): PDOStatement
    {
        if ($options === null) {
            $options = [];
        }

        $stmt = $this->pdo->prepare($statement, $options);

        $this->logQuery($statement);

        return $stmt;
    }

    private function logQuery($query)
    {
        $this->logger->info("Executing query: {$query}");
    }
}

<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use App\Infrastructure\Database\QueryLoggerPDO;

class DBConnection
{
    private $pdo;

    public function __construct(array $dbConfig, private mixed $logger)
    {
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo = new QueryLoggerPDO($pdo, $this->logger);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getPdo(): QueryLoggerPDO
    {
        return $this->pdo;
    }
}

<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class DBConnection
{
    private $pdo;

    public function __construct(array $dbConfig)
    {
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}

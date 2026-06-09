<?php

namespace App\Services;

use App\Contracts\DatabaseServiceInterface;
use App\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

class MySqlDatabaseService implements DatabaseServiceInterface
{
    public function testConnection(string $host, int $port, string $username, string $password): void
    {
        try {
            new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_TIMEOUT => 5, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    public function getDatabases(string $host, int $port, string $username, string $password): array
    {
        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );

            $statement = $pdo->query('SHOW DATABASES');

            return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'Database');
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }
}

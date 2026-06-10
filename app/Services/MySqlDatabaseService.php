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
            $pdo = $this->createConnection($host, $port, $username, $password);
            $statement = $pdo->query('SHOW DATABASES');

            return array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'Database');
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    public function getTables(string $host, int $port, string $username, string $password, string $database): array
    {
        try {
            $pdo = $this->createConnection($host, $port, $username, $password);
            $stmt = $pdo->prepare(
                'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            );
            $stmt->execute([$database]);

            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'TABLE_NAME');
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    public function getDatabaseStats(string $host, int $port, string $username, string $password, string $database): array
    {
        try {
            $pdo = $this->createConnection($host, $port, $username, $password);

            $stmt = $pdo->prepare(
                'SELECT DEFAULT_CHARACTER_SET_NAME as charset, DEFAULT_COLLATION_NAME as collation
                 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
            );
            $stmt->execute([$database]);
            $schema = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare(
                'SELECT COALESCE(SUM(DATA_LENGTH + INDEX_LENGTH), 0) as total_size, COUNT(*) as table_count
                 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?',
            );
            $stmt->execute([$database]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'charset' => $schema['charset'] ?? 'unknown',
                'collation' => $schema['collation'] ?? 'unknown',
                'total_size' => (int) ($stats['total_size'] ?? 0),
                'table_count' => (int) ($stats['table_count'] ?? 0),
            ];
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    public function getTableColumns(string $host, int $port, string $username, string $password, string $database, string $table): array
    {
        try {
            $pdo = $this->createConnection($host, $port, $username, $password);
            $stmt = $pdo->prepare(
                'SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
            );
            $stmt->execute([$database, $table]);

            return array_map(fn (array $row) => [
                'name' => $row['COLUMN_NAME'],
                'data_type' => $row['DATA_TYPE'],
                'column_type' => $row['COLUMN_TYPE'],
                'nullable' => $row['IS_NULLABLE'] === 'YES',
                'default' => $row['COLUMN_DEFAULT'],
                'key' => $row['COLUMN_KEY'],
                'extra' => $row['EXTRA'],
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    public function getTableData(string $host, int $port, string $username, string $password, string $database, string $table, int $page, int $perPage): array
    {
        try {
            $pdo = $this->createConnection($host, $port, $username, $password);

            $quotedTable = "`{$database}`.`{$table}`";

            $countStmt = $pdo->query("SELECT COUNT(*) FROM {$quotedTable}");
            $total = (int) $countStmt->fetchColumn();

            $offset = ($page - 1) * $perPage;
            $stmt = $pdo->prepare("SELECT * FROM {$quotedTable} LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
            ];
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage(), previous: $e);
        }
    }

    private function createConnection(string $host, int $port, string $username, string $password): PDO
    {
        return new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }
}

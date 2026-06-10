<?php

namespace App\Contracts;

use App\Exceptions\DatabaseConnectionException;

interface DatabaseServiceInterface
{
    /**
     * @throws DatabaseConnectionException
     */
    public function testConnection(string $host, int $port, string $username, string $password): void;

    /**
     * @return array<int, string>
     *
     * @throws DatabaseConnectionException
     */
    public function getDatabases(string $host, int $port, string $username, string $password): array;

    /**
     * @return array<int, string>
     *
     * @throws DatabaseConnectionException
     */
    public function getTables(string $host, int $port, string $username, string $password, string $database): array;

    /**
     * @return array{charset: string, collation: string, total_size: int, table_count: int}
     *
     * @throws DatabaseConnectionException
     */
    public function getDatabaseStats(string $host, int $port, string $username, string $password, string $database): array;

    /**
     * @return array<int, array{name: string, data_type: string, column_type: string, nullable: bool, default: ?string, key: string, extra: string}>
     *
     * @throws DatabaseConnectionException
     */
    public function getTableColumns(string $host, int $port, string $username, string $password, string $database, string $table): array;

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     *
     * @throws DatabaseConnectionException
     */
    public function getTableData(string $host, int $port, string $username, string $password, string $database, string $table, int $page, int $perPage): array;
}

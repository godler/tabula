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
     * @throws DatabaseConnectionException
     */
    public function getDatabases(string $host, int $port, string $username, string $password): array;
}

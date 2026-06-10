<?php

namespace App\Table\Columns;

abstract class Column
{
    public function __construct(
        public readonly string $name,
        public readonly string $dataType,
        public readonly string $columnType,
        public readonly bool $nullable,
        public readonly ?string $default,
        public readonly string $key,
        public readonly string $extra,
    ) {}

    abstract public function format(mixed $value): string;

    abstract public function typeLabel(): string;

    abstract public function badgeColor(): string;

    public function isPrimaryKey(): bool
    {
        return $this->key === 'PRI';
    }

    public function isAutoIncrement(): bool
    {
        return str_contains($this->extra, 'auto_increment');
    }
}

<?php

namespace App\Table\Columns;

class JsonColumn extends Column
{
    public function format(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return (string) $value;
        }

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '';
    }

    public function typeLabel(): string
    {
        return 'json';
    }

    public function badgeColor(): string
    {
        return 'amber';
    }
}

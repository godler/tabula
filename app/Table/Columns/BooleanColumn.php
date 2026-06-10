<?php

namespace App\Table\Columns;

class BooleanColumn extends Column
{
    public function format(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'true' : 'false';
    }

    public function typeLabel(): string
    {
        return 'boolean';
    }

    public function badgeColor(): string
    {
        return 'violet';
    }
}

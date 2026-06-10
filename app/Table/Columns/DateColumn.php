<?php

namespace App\Table\Columns;

class DateColumn extends Column
{
    public function format(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    public function typeLabel(): string
    {
        return 'date';
    }

    public function badgeColor(): string
    {
        return 'lime';
    }
}

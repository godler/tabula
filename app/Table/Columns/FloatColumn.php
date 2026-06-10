<?php

namespace App\Table\Columns;

class FloatColumn extends Column
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
        return $this->columnType;
    }

    public function badgeColor(): string
    {
        return 'cyan';
    }
}

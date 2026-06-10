<?php

namespace App\Table\Columns;

class UnknownColumn extends Column
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
        return $this->dataType;
    }

    public function badgeColor(): string
    {
        return 'zinc';
    }
}

<?php

namespace App\Table\Columns;

class IntegerColumn extends Column
{
    public function format(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return number_format((int) $value, 0, '.', ' ');
    }

    public function typeLabel(): string
    {
        return $this->dataType;
    }

    public function badgeColor(): string
    {
        return 'blue';
    }
}

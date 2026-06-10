<?php

namespace App\Table;

use App\Table\Columns\BooleanColumn;
use App\Table\Columns\Column;
use App\Table\Columns\DateColumn;
use App\Table\Columns\DateTimeColumn;
use App\Table\Columns\FloatColumn;
use App\Table\Columns\IntegerColumn;
use App\Table\Columns\JsonColumn;
use App\Table\Columns\StringColumn;
use App\Table\Columns\UnknownColumn;

class ColumnFactory
{
    /**
     * @param  array{name: string, data_type: string, column_type: string, nullable: bool, default: ?string, key: string, extra: string}  $columnData
     */
    public static function make(array $columnData): Column
    {
        $args = [
            $columnData['name'],
            $columnData['data_type'],
            $columnData['column_type'],
            $columnData['nullable'],
            $columnData['default'],
            $columnData['key'],
            $columnData['extra'],
        ];

        if ($columnData['column_type'] === 'tinyint(1)') {
            return new BooleanColumn(...$args);
        }

        return match ($columnData['data_type']) {
            'int', 'bigint', 'smallint', 'mediumint', 'tinyint' => new IntegerColumn(...$args),
            'float', 'double', 'decimal', 'numeric' => new FloatColumn(...$args),
            'date' => new DateColumn(...$args),
            'datetime', 'timestamp' => new DateTimeColumn(...$args),
            'json' => new JsonColumn(...$args),
            'varchar', 'char', 'text', 'mediumtext', 'longtext', 'tinytext', 'enum', 'set' => new StringColumn(...$args),
            default => new UnknownColumn(...$args),
        };
    }
}

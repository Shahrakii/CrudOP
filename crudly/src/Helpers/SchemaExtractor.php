<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaExtractor
{
    public static function getTableColumns(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $enriched = [];

        foreach ($columns as $col) {
            $columnInfo = DB::selectOne("SHOW FULL COLUMNS FROM `{$table}` WHERE Field = ?", [$col]);
            $type = $columnInfo->Type;

            $generalType = self::mapType($type);
            $enumValues = self::extractEnumValues($type);

            $relatedTable = self::getForeignTable($table, $col);

            $enriched[] = [
                'name'          => $col,
                'general_type'  => $generalType,
                'nullable'      => ($columnInfo->Null === 'YES'),
                'enum_values'   => $enumValues,
                'related_table' => $relatedTable,
            ];
        }

        return $enriched;
    }

    protected static function mapType(string $type): string
    {
        $type = strtolower($type);

        if (str_starts_with($type, 'int')) return 'integer';
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'char')) return 'string';
        if ($type === 'text') return 'text';
        if ($type === 'date') return 'date';
        if ($type === 'datetime' || $type === 'timestamp') return 'datetime';
        if ($type === 'tinyint(1)') return 'boolean';
        if (str_starts_with($type, 'enum')) return 'enum';

        return 'string';
    }

    protected static function extractEnumValues(string $type): array
    {
        if (!str_starts_with($type, 'enum')) return [];
        preg_match("/^enum\((.*)\)$/", $type, $matches);
        if (!isset($matches[1])) return [];
        return array_map(fn($v) => trim($v, "'"), explode(',', $matches[1]));
    }

    protected static function getForeignTable(string $table, string $column): ?string
    {
        $result = DB::selectOne("
            SELECT REFERENCED_TABLE_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ", [$table, $column]);

        return $result->REFERENCED_TABLE_NAME ?? null;
    }
}
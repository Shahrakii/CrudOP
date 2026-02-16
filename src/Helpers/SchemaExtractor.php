<?php

namespace Shahrakii\Crudly\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaExtractor
{
    /**
     * Get detailed column information for a table
     */
    public function getTableColumns(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $enriched = [];

        foreach ($columns as $col) {
            $columnInfo = DB::selectOne(
                "SHOW FULL COLUMNS FROM `{$table}` WHERE Field = ?",
                [$col]
            );

            if (!$columnInfo) {
                continue;
            }

            $type = $columnInfo->Type;
            $generalType = $this->mapType($type);
            $enumValues = $this->extractEnumValues($type);
            $relatedTable = $this->getForeignTable($table, $col);
            $isNullable = $columnInfo->Null === 'YES';
            $isPrimaryKey = $columnInfo->Key === 'PRI';
            $isAutoIncrement = strpos($columnInfo->Extra, 'auto_increment') !== false;

            $enriched[] = [
                'name' => $col,
                'general_type' => $relatedTable ? 'foreign_key' : $generalType,
                'type' => $type,
                'nullable' => $isNullable,
                'enum_values' => $enumValues,
                'related_table' => $relatedTable,
                'is_primary_key' => $isPrimaryKey,
                'is_auto_increment' => $isAutoIncrement,
                'default' => $columnInfo->Default,
            ];
        }

        return $enriched;
    }

    /**
     * Map database type to general type
     */
    protected function mapType(string $type): string
    {
        $type = strtolower($type);

        if (str_starts_with($type, 'int') || str_starts_with($type, 'bigint') || str_starts_with($type, 'smallint')) {
            return 'integer';
        }
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'char')) {
            return 'string';
        }
        if ($type === 'text' || str_starts_with($type, 'longtext')) {
            return 'text';
        }
        if (str_starts_with($type, 'date') && !str_contains($type, 'time')) {
            return 'date';
        }
        if (str_contains($type, 'datetime') || str_contains($type, 'timestamp')) {
            return 'datetime';
        }
        if ($type === 'tinyint(1)' || str_starts_with($type, 'bool')) {
            return 'boolean';
        }
        if (str_starts_with($type, 'enum')) {
            return 'enum';
        }
        if (str_starts_with($type, 'decimal') || str_starts_with($type, 'float') || str_starts_with($type, 'double')) {
            return 'decimal';
        }
        if ($type === 'json') {
            return 'json';
        }

        return 'string';
    }

    /**
     * Extract enum values from type definition
     */
    protected function extractEnumValues(string $type): array
    {
        if (!str_starts_with(strtolower($type), 'enum')) {
            return [];
        }

        preg_match("/^enum\((.*)\)$/i", $type, $matches);

        if (!isset($matches[1])) {
            return [];
        }

        return array_map(
            fn($v) => trim($v, "'\""),
            explode(',', $matches[1])
        );
    }

    /**
     * Get related table from foreign key
     */
    protected function getForeignTable(string $table, string $column): ?string
    {
        try {
            $result = DB::selectOne("
                SELECT REFERENCED_TABLE_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table, $column]);

            return $result?->REFERENCED_TABLE_NAME;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get column length from type
     */
    public function getColumnLength(string $type): ?int
    {
        if (preg_match('/\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}

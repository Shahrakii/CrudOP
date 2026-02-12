<?php

namespace App\Helpers;

class Filter
{
    protected static array $globalFilters;
    protected static array $tableFilters;

    public static function init(): void
    {
        // Load filters from the separate files
        self::$globalFilters = require base_path('app/Filters/GlobalFilter.php');
        self::$tableFilters = require base_path('app/Filters/TableFilter.php');

        // Ensure tableFilters is always an array
        if (!is_array(self::$tableFilters)) {
            self::$tableFilters = [];
        }
    }

    public static function filterColumns(array $columns, string $table): array
    {
        if (!isset(self::$globalFilters)) {
            self::init();
        }

        // Table-specific filters for this table
        $tableSpecific = self::$tableFilters[$table] ?? [];

        $exclude = array_merge(
            self::$globalFilters,
            $tableSpecific
        );

        return array_filter($columns, fn($col) => !in_array($col['name'], $exclude));
    }

    public static function addGlobalFilter(string $column): void
    {
        self::$globalFilters[] = $column;
    }

    public static function addTableFilter(string $table, string $column): void
    {
        if (!isset(self::$tableFilters[$table])) {
            self::$tableFilters[$table] = [];
        }
        self::$tableFilters[$table][] = $column;
    }
}
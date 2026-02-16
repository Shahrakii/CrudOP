<?php

namespace Shahrakii\Crudly;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Shahrakii\Crudly\Helpers\SchemaExtractor;
use Shahrakii\Crudly\Helpers\ValidationGenerator;

class Crudly
{
    protected $app;
    protected $schemaExtractor;
    protected $validationGenerator;

    public function __construct($app)
    {
        $this->app = $app;
        $this->schemaExtractor = new SchemaExtractor();
        $this->validationGenerator = new ValidationGenerator();
    }

    /**
     * Get all columns for a table with types
     */
    public function getTableColumns(string $table): array
    {
        return $this->schemaExtractor->getTableColumns($table);
    }

    /**
     * Get filtered columns (excluding system columns)
     */
    public function getFilteredColumns(string $table): array
    {
        $columns = $this->getTableColumns($table);
        $filters = config('crudly.global_filters', []);

        return array_filter($columns, function ($col) use ($filters) {
            return !in_array($col['name'], $filters);
        });
    }

    /**
     * Generate validation rules for table
     */
    public function getValidationRules(string $table): array
    {
        $columns = $this->getFilteredColumns($table);
        return $this->validationGenerator->generateRules($columns);
    }

    /**
     * Check if table exists
     */
    public function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * Get table relationships (foreign keys)
     */
    public function getRelationships(string $table): array
    {
        $columns = $this->getTableColumns($table);
        return array_filter($columns, fn($col) => $col['general_type'] === 'foreign_key');
    }

    /**
     * Format column name to label
     */
    public static function formatColumnLabel(string $column): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $column));
    }

    /**
     * Get all tables in database
     */
    public function getAllTables(): array
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
        return array_filter($tables, function ($table) {
            return !in_array($table, config('crudly.exclude_tables', [
                'migrations',
                'failed_jobs',
                'password_resets',
                'personal_access_tokens',
            ]));
        });
    }
}

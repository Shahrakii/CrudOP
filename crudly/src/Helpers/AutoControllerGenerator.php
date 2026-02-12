<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoControllerGenerator
{
    public static ?string $modelClass = null;
    protected static string $table;
    protected static array $columns;
    protected static array $filteredColumns;
    protected static array $rules;

    /**
     * Initialize helper for a given model
     */
    public static function init(string $modelClass): void
    {
        self::$modelClass = $modelClass;
        $instance = new $modelClass;
        self::$table = $instance->getTable();

        // Auto fetch columns
        self::$columns = self::getTableColumns(self::$table);

        // Apply filters
        self::$filteredColumns = \App\Helpers\Filter::filterColumns(self::$columns, self::$table);

        // Generate validation rules
        self::$rules = self::generateRules(self::$filteredColumns);
    }

    /**
     * Detect table columns with types, enums, foreign keys
     */
    public static function getTableColumns(string $table): array
    {
        $columns = DB::select("SHOW FULL COLUMNS FROM `$table`");
        $result = [];

        foreach ($columns as $col) {
            $type = $col->Type;
            $general_type = 'string';
            $enum_values = [];

            if (str_contains($type, 'int')) $general_type = 'integer';
            elseif (str_contains($type, 'text')) $general_type = 'text';
            elseif (str_contains($type, 'enum')) {
                $general_type = 'enum';
                preg_match("/enum\((.*)\)/", $type, $matches);
                $enum_values = array_map(fn($v)=>trim($v,"'"), explode(',', $matches[1]));
            } elseif (str_contains($type,'date')) $general_type = 'date';
            elseif (str_contains($type,'bool')) $general_type = 'boolean';

            $is_nullable = $col->Null === 'YES';
            $related_table = null;

            // Detect foreign key
            $fk = DB::select("
                SELECT REFERENCED_TABLE_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA=DATABASE()
                  AND TABLE_NAME=?
                  AND COLUMN_NAME=?
            ", [$table, $col->Field]);

            if($fk) $related_table = $fk[0]->REFERENCED_TABLE_NAME;

            $result[] = [
                'name'          => $col->Field,
                'general_type'  => $related_table ? 'foreign_key' : $general_type,
                'nullable'      => $is_nullable,
                'enum_values'   => $enum_values,
                'related_table' => $related_table
            ];
        }

        return $result;
    }

    /**
     * Generate validation rules automatically
     */
    protected static function generateRules(array $columns): array
    {
        $rules = [];
        foreach ($columns as $col) {
            switch ($col['general_type']) {
                case 'string':
                case 'text':
                    $rules[$col['name']] = ($col['nullable'] ? 'nullable' : 'required') . '|string|max:255';
                    break;
                case 'integer':
                    $rules[$col['name']] = ($col['nullable'] ? 'nullable' : 'required') . '|integer';
                    break;
                case 'boolean':
                    $rules[$col['name']] = 'required|boolean';
                    break;
                case 'date':
                    $rules[$col['name']] = ($col['nullable'] ? 'nullable' : 'required') . '|date';
                    break;
                case 'enum':
                    $rules[$col['name']] = ($col['nullable'] ? 'nullable' : 'required') . '|in:' . implode(',', $col['enum_values'] ?? []);
                    break;
                case 'foreign_key':
                    $rules[$col['name']] = ($col['nullable'] ? 'nullable' : 'required') . '|exists:' . $col['related_table'] . ',id';
                    break;
                default:
                    $rules[$col['name']] = 'nullable';
            }
        }
        return $rules;
    }

    /**
     * Render view with columns & values
     */
    public static function handleView(string $function, $id = null)
    {
        if (!self::$modelClass) throw new \Exception('Model class not set.');

        $modelClass = self::$modelClass;
        $columns = self::$filteredColumns;
        $values = [];

        if ($id) $values = $modelClass::find($id)?->toArray() ?? [];

        return view('autocontroller.form', [
            'columns' => $columns,
            'values'  => $values
        ]);
    }

    /**
     * Handle store/update automatically
     */
    public static function handleRequest(string $functionName, Request $request, $id = null)
    {
        if (!self::$modelClass) throw new \Exception('Model class not set.');
        $validated = $request->validate(self::$rules);
        $modelClass = self::$modelClass;

        if ($functionName === 'store') $modelClass::create($validated);
        elseif ($functionName === 'update') {
            $model = $modelClass::findOrFail($id);
            $model->update($validated);
        }

        return $validated;
    }

    public static function getRules(): array { return self::$rules; }
    public static function getColumns(): array { return self::$filteredColumns; }
}
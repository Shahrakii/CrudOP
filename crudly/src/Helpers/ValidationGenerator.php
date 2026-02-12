<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ValidationGenerator
{
    /**
     * Generate rules array for a given table schema
     */
    public static function generateRules(array $columns): array
    {
        $rules = [];

        foreach ($columns as $col) {
            if (in_array($col['key'], ['PRI', 'MUL']) && $col['extra'] === 'auto_increment') {
                continue; // skip auto-increment primary keys
            }

            $fieldRules = [];

            if (!$col['nullable']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($col['general_type']) {
                case 'string':
                    $fieldRules[] = 'string';
                    if ($col['length']) $fieldRules[] = 'max:' . $col['length'];
                    break;
                case 'integer':
                    $fieldRules[] = 'integer';
                    break;
                case 'float':
                    $fieldRules[] = 'numeric';
                    break;
                case 'boolean':
                    $fieldRules[] = 'boolean';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'enum':
                    $fieldRules[] = 'in:' . implode(',', $col['enum_values']);
                    break;
                case 'json':
                    $fieldRules[] = 'json';
                    break;
            }

            $rules[$col['name']] = implode('|', $fieldRules);
        }

        return $rules;
    }
}
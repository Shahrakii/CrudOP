<?php

namespace Shahrakii\Crudly\Helpers;

class ValidationGenerator
{
    /**
     * Generate Laravel validation rules from columns
     */
    public function generateRules(array $columns): array
    {
        $rules = [];

        foreach ($columns as $col) {
            // Skip primary keys and auto-increment
            if ($col['is_primary_key'] && $col['is_auto_increment']) {
                continue;
            }

            $fieldRules = [];

            // Required or nullable
            if (!$col['nullable']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-specific rules
            match ($col['general_type']) {
                'string' => $this->addStringRules($fieldRules, $col),
                'integer' => $this->addIntegerRules($fieldRules, $col),
                'decimal' => $this->addDecimalRules($fieldRules, $col),
                'boolean' => $this->addBooleanRules($fieldRules, $col),
                'date' => $this->addDateRules($fieldRules, $col),
                'datetime' => $this->addDatetimeRules($fieldRules, $col),
                'enum' => $this->addEnumRules($fieldRules, $col),
                'foreign_key' => $this->addForeignKeyRules($fieldRules, $col),
                'text' => $this->addTextRules($fieldRules, $col),
                'json' => $this->addJsonRules($fieldRules, $col),
                default => null,
            };

            // Add common validation rules based on column name
            if (str_contains($col['name'], 'email')) {
                $fieldRules[] = 'email';
            }
            if (str_contains($col['name'], 'url')) {
                $fieldRules[] = 'url';
            }
            if (str_contains($col['name'], 'phone')) {
                $fieldRules[] = 'regex:/^[0-9+\-() ]+$/';
            }

            $rules[$col['name']] = implode('|', array_filter($fieldRules));
        }

        return $rules;
    }

    /**
     * Add string-specific rules
     */
    protected function addStringRules(array &$rules, array $col): void
    {
        $rules[] = 'string';

        // Get max length from type
        $length = $this->getMaxLength($col['type']);
        if ($length) {
            $rules[] = "max:{$length}";
        } else {
            $rules[] = 'max:255';
        }
    }

    /**
     * Add integer-specific rules
     */
    protected function addIntegerRules(array &$rules, array $col): void
    {
        $rules[] = 'integer';
    }

    /**
     * Add decimal-specific rules
     */
    protected function addDecimalRules(array &$rules, array $col): void
    {
        $rules[] = 'numeric';
    }

    /**
     * Add boolean-specific rules
     */
    protected function addBooleanRules(array &$rules, array $col): void
    {
        $rules[] = 'boolean';
    }

    /**
     * Add date-specific rules
     */
    protected function addDateRules(array &$rules, array $col): void
    {
        $rules[] = 'date';
    }

    /**
     * Add datetime-specific rules
     */
    protected function addDatetimeRules(array &$rules, array $col): void
    {
        $rules[] = 'date_format:Y-m-d H:i:s';
    }

    /**
     * Add enum-specific rules
     */
    protected function addEnumRules(array &$rules, array $col): void
    {
        if (!empty($col['enum_values'])) {
            $values = implode(',', $col['enum_values']);
            $rules[] = "in:{$values}";
        }
    }

    /**
     * Add foreign key-specific rules
     */
    protected function addForeignKeyRules(array &$rules, array $col): void
    {
        if ($col['related_table']) {
            $rules[] = "exists:{$col['related_table']},id";
        }
    }

    /**
     * Add text-specific rules
     */
    protected function addTextRules(array &$rules, array $col): void
    {
        $rules[] = 'string';
    }

    /**
     * Add JSON-specific rules
     */
    protected function addJsonRules(array &$rules, array $col): void
    {
        $rules[] = 'json';
    }

    /**
     * Extract max length from column type
     */
    protected function getMaxLength(string $type): ?int
    {
        if (preg_match('/\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}

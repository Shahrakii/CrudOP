<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ColumnInputMapper
{
    protected static array $typeMapping = [
        'string'      => 'text',
        'integer'     => 'number',
        'boolean'     => 'checkbox',
        'text'        => 'textarea',
        'date'        => 'date',
        'datetime'    => 'datetime-local',
        'enum'        => 'select',
        'foreign_key' => 'select',
        'email'       => 'email',
        'password'    => 'password',
        'file'        => 'file'
    ];

    protected static array $customTypeMap = [
        'description' => 'text',
        'content'     => 'ckeditor',
        'profile_picture' => 'file'
    ];

    protected static ?array $styles = null;
    protected static bool $cdnLoaded = false;
    protected static ?string $cdn = null;

    public static function initStyles(): void
    {
        if (self::$styles === null) {
            self::$styles = require base_path('app/Helpers/InputStyles.php');
        }

        if (!self::$cdnLoaded) {
            $framework = env('CSS_FRAMEWORK', 'tailwind');

            if ($framework === 'tailwind') {
                self::$cdn = '<script src="https://cdn.tailwindcss.com"></script>';
            } elseif ($framework === 'bootstrap') {
                self::$cdn = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
            } else {
                self::$cdn = '';
            }

            self::$cdnLoaded = true;
        }
    }

    public static function getCDN(): string
    {
        self::initStyles();
        return self::$cdn ?? '';
    }

    public static function getInput(array $column, $value = null)
    {
        self::initStyles();

        if(isset(self::$customTypeMap[$column['name']])) {
            $column['custom_type'] = self::$customTypeMap[$column['name']];
        }

        $type = $column['custom_type'] ?? self::$typeMapping[$column['general_type']] ?? 'text';
        $attrs = self::$styles[$type] ?? '';

        switch ($type) {
            case 'select':
                if(!empty($column['related_table'] ?? null)) {
                    $tableColumns = Schema::getColumnListing($column['related_table']);
                    $displayColumn = in_array('name', $tableColumns) ? 'name' : ($tableColumns[1] ?? 'id');
                    $items = DB::table($column['related_table'])->pluck($displayColumn,'id');

                    $options = implode('', array_map(
                        fn($id,$n)=>"<option value='{$id}'".($value==$id?' selected':'').">{$n}</option>",
                        array_keys($items->toArray()),
                        $items->toArray()
                    ));

                    return "<select name='{$column['name']}' class='{$attrs}'>{$options}</select>";
                }

                if(isset($column['enum_values'])) {
                    $options = implode('', array_map(
                        fn($opt) => "<option value='{$opt}'".($value==$opt?' selected':'').">{$opt}</option>",
                        $column['enum_values']
                    ));
                    return "<select name='{$column['name']}' class='{$attrs}'>{$options}</select>";
                }
                break;
        }

        return "<input type='{$type}' name='{$column['name']}' value='{$value}' class='{$attrs}'>";
    }
}
<?php

namespace Shahrakii\Crudly\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getTableColumns(string $table)
 * @method static array getFilteredColumns(string $table)
 * @method static array getValidationRules(string $table)
 * @method static bool tableExists(string $table)
 * @method static array getRelationships(string $table)
 * @method static array getAllTables()
 * @method static string formatColumnLabel(string $column)
 *
 * @see \Shahrakii\Crudly\Crudly
 */
class Crudly extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'crudly';
    }
}

<?php

namespace Hexify\LaraIdCustomizer;

use Exception;
use Hexify\LaraIdCustomizer\Helpers\Random;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB, Schema;

class IdCustomizer implements IdFactory
{
    private static $model;
    private static string $column;
    private static int $length;
    private static string $prefix;
    private static string $factory_method;
    private static bool $reset_on_prefix_change;
    private static string $set;
    private static string $extra;

    public static function validateConfig(array $config){
        isset($config['model']) 
            ?: throw new Exception('You must specify the model in the $config[\'model\'];');

        is_subclass_of($config['model'], Model::class)
            ?: throw new Exception($config['model'].' is not a Model.');
        
        static::$model = $config['model'];

        $table = (new static::$model)->getTable();

        static::$column = $config['column'] ?? (new static::$model)->getKeyName();

        Schema::hasColumn($table, static::$column)
            ?: throw new Exception('The column '.static::$column." does not exist in $table table.");
        
        $column_type = Schema::getColumnType($table, static::$column);

        static::$length = $config['length'] ?? 10;

        static::$prefix = '';
        if (isset($config['prefix'])) {
            (is_numeric($config['prefix']) || is_string($config['prefix']))
                ?: throw new Exception('The prefix option should be numeric | string');

            static::$prefix = ''.$config['prefix'];
            
            in_array($column_type, ['int', 'integer', 'bigint', 'numeric']) && !is_numeric(static::$prefix)
                ? throw new Exception(static::$column." column type is $column_type but prefix is not numeric.")
                :NULL;
        }

        static::$factory_method = $config['factory_method'] ?? static::INCREMENTAL;

        (static::$factory_method == static::INCREMENTAL || static::$factory_method == static::RANDOM) ?: 
            throw new Exception('The $config[\'factory_method\'] option should be '.static::RANDOM.' | '.static::INCREMENTAL.'.');

        (static::$factory_method == static::INCREMENTAL && (isset($config['set']) || isset($config['extra'])))
            ? throw new Exception('ID Customizer Error
                $config[\'factory_method\'] is '.static::INCREMENTAL.', $config[\'set\'] and $config[\'extra\'] options are not acceptable.'): NULL;
        
        (static::$factory_method == static::RANDOM && isset($config['reset_on_prefix_change']))
            ? throw new Exception('ID Customizer Error
                $config[\'factory_method\'] is '.static::RANDOM.', $config[\'reset_on_prefix_change\'] option is not acceptable.'): NULL;

        static::$reset_on_prefix_change = $config['reset_on_prefix_change'] ?? true;
        static::$set = $config['set'] ?? Random::ALPHA_NUMERIC;
        static::$extra = $config['extra'] ?? '';
    }

    public static function generate(array $config)
    {
        static::validateConfig($config);
        return match(static::$factory_method){
            static::INCREMENTAL =>
                static::incremental(
                    static::$model,
                    static::$column, 
                    static::$length, 
                    static::$prefix, 
                    static::$reset_on_prefix_change
                ),
            static::RANDOM =>
                static::random(
                    static::$model,
                    static::$column, 
                    static::$length, 
                    static::$prefix, 
                    static::$set,
                    static::$extra
                )
        };
    }
    private static function incremental($model, string $column, int $length, $prefix, bool $reset_on_prefix_change)
    {
        $table = (new $model)->getTable();
        $prefix_length = strlen($prefix);
        $id_length = $length - $prefix_length;

        if($model::all()->isEmpty()){
            return $prefix . str_pad(1, $id_length, '0', STR_PAD_LEFT);
        }

        $reset_on_prefix_change
            ? $maxQuery = sprintf("SELECT MAX(SUBSTR(%s, LENGTH('%s '))) AS max_value FROM %s WHERE %s LIKE %s", $column, $prefix, $table, $column, "'" . $prefix . "%'")
            : $maxQuery = sprintf("SELECT MAX(SUBSTR(%s, LENGTH('%s '))) AS max_value FROM %s", $column, $prefix, $table);

        $queryResult = DB::select($maxQuery);
        $max_value = $queryResult[0]->max_value;

        return $prefix . str_pad((int)$max_value + 1, $id_length, '0', STR_PAD_LEFT);
    }

    private static function random($model, string $column, int $length, $prefix, string $set, string $extra)
    {
        $prefix_length = strlen($prefix);
        $value_length = $length - $prefix_length;

        do {
            $generated_value = Random::generate($value_length,$set,$extra);
        } while (self::isValueExists($model, $column, $generated_value));

        return $prefix.$generated_value;
    }

    private static function isValueExists($model, $column, $value)
    {
        return $model::where($column , $value)->exists();
    }
}

<?php

namespace Hexify\LaraIdCustomizer\Traits;

use Hexify\LaraIdCustomizer\IdCustomizer;

trait HasIdFactory
{
    public static function bootHasIdFactory()
    {
        self::creating(function ($model) {
            self::$idFactoryConfig['model'] = get_class($model);
            IdCustomizer::validateConfig(self::$idFactoryConfig);
            $column = self::$idFactoryConfig['column'];
            $model->{$column} = IdCustomizer::generate( self::$idFactoryConfig);
        });
    }
}
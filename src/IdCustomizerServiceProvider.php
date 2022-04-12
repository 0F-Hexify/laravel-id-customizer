<?php

namespace Hexify\LaraIdCustomizer;

use Illuminate\Support\ServiceProvider;

class IdCustomizerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('\Hexify\LaraIdCustomizer\IdCustomizer');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

}

<?php

namespace luiscastror9\MovistarM2Mglobal;

use Illuminate\Support\ServiceProvider;

class MovistarM2MglobalServiceProvider extends ServiceProvider {

    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot() {
        
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register() {

        $this->mergeConfigFrom(dirname(__FILE__) . '/../config/MovistarM2Mglobal.php', 'MovistarM2Mglobal');


        $this->app->singleton('MovistarM2Mglobal', function() {

            return new MovistarM2Mglobal();
        });

        $this->app->booting(function() {

            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            $loader->alias('MovistarM2Mglobal', 'luiscastror9\MovistarM2Mglobal\Facades\MovistarM2Mglobal');
        });
        $this->publishes([
            dirname(__FILE__) . '/../config/MovistarM2Mglobal.php' => config_path('MovistarM2Mglobal.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['MovistarM2Mglobal'];
    }

}

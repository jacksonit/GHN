<?php

namespace Jacksonit\GHN;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

/**
 * ServiceProvider
 *
 * The service provider for the modules. After being registered
 * it will make sure that each of the modules are properly loaded
 * i.e. with their routes, views etc.
 *
 * @author Cao Son <son.caoxuan92@gmail.com>
 * @package Jacksonit\GHN
 */
class GHNServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/config/ghn.php' => config_path('ghn.php'),
        ]);
    }

    public function register()
    {
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('GHN', 'Jacksonit\GHN\Facades\GHNCharge');
        });

        $this->app->bind('GHNCharge', GHNCharge::class);
    }
}
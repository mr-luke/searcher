<?php

namespace Mrluke\Searcher;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

/**
 * ServiceProvider for package.
 *
 * @author    Łukasz Sitnicki (mr-luke)
 * @link      http://github.com/mr-luke/searcher
 *
 * @category  Laravel
 * @package   mr-luke/searcher
 * @license   MIT
 */
class SearcherServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ .'/../config/searcher.php' => config_path('searcher.php')]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ .'/../config/searcher.php', 'searcher');

        $this->app->bind('mrluke-searcher', function ($app) {
            return new \Mrluke\Searcher\Searcher($app['config']->get('searcher'));
        });
    }
}

<?php

namespace Laravolt\Password;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class PackageServiceProvider
 *
 * @package Laravolt\Password
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('password', function($app){
            $app['config']['auth.password.email'] = $app['config']['password.emails.reset'];
            return new Password($app['auth.password.broker'], $app['mailer'], $app['config']['password.emails.new']);
        });
    }

    /**
     * Application is booting
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViews();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerConfigurations();
    }

    /**
     * Register the package views
     *
     * @return void
     */
    protected function registerViews()
    {
        // register views within the application with the set namespace
        $this->loadViewsFrom($this->packagePath('resources/views'), 'password');
        // allow views to be published to the storage directory
        $this->publishes([
            $this->packagePath('resources/views') => base_path('resources/views/laravolt/password'),
        ], 'views');
    }

    /**
     * Register the package migrations
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (version_compare($this->app->version(), '5.3.0', '>=')) {
            $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        } else {
            $this->publishes([
                $this->packagePath('database/migrations') => database_path('/migrations')
            ], 'migrations');
        }
    }

    /**
     * Register the package translations
     *
     * @return void
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'password');
    }

    /**
     * Register the package configurations
     *
     * @return void
     */
    protected function registerConfigurations()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/config.php'), 'password'
        );
        $this->publishes([
            $this->packagePath('config/config.php') => config_path('password.php'),
        ], 'config');
    }

    /**
     * Loads a path relative to the package base directory
     *
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf("%s/../%s", __DIR__ , $path);
    }
}

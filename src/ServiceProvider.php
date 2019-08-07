<?php

namespace Laravolt\Password;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

/**
 * Class PackageServiceProvider
 *
 * @package Laravolt\Password
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('password', function ($app) {
            $app['config']['auth.password.email'] = $app['config']['laravolt.password.emails.reset'];
            $config = $this->app['config']["auth.passwords.users"];
            $token = $this->createTokenRepository($config);

            return new Password($token, $app['mailer'], $app['config']['laravolt.password.emails.new']);
        });
    }

    /**
     * Application is booting
     *
     * @return void
     */
    public function boot()
    {
        $this->bootConfigurations();
        $this->bootViews();
        $this->bootMigrations();
        $this->bootTranslations();
    }

    /**
     * Register the package views
     *
     * @return void
     */
    protected function bootViews()
    {
        $this->loadViewsFrom($this->packagePath('resources/views'), 'password');

        $this->publishes([
            $this->packagePath('resources/views') => base_path('resources/views/laravolt/password'),
        ], 'views');
    }

    protected function bootMigrations()
    {
        $this->publishes(
            [
                $this->packagePath('database/migrations/add_password_last_set_to_users.php') => $this->getMigrationFileName(),
            ],
            'migrations'
        );
    }

    protected function bootTranslations()
    {
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'password');
    }

    protected function bootConfigurations()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/config.php'), 'laravolt.password'
        );
        $this->publishes([
            $this->packagePath('config/config.php') => config_path('laravolt/password.php'),
        ], 'config');
    }

    /**
     * Loads a path relative to the package base directory
     *
     * @param  string  $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf("%s/../%s", __DIR__, $path);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @return string
     */
    protected function getMigrationFileName(): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) {
                return File::glob($path.'*_add_password_last_set_to_users.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_add_password_last_set_to_users.php")
            ->first();
    }

    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        $key = $this->app['config']['app.key'];

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $connection = $config['connection'] ?? null;

        return new DatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $this->app['hash'],
            $config['table'],
            $key,
            $config['expire']
        );
    }
}

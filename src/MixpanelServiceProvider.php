<?php

namespace Secrethash\Mixpanel;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class MixpanelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');
            $this->registerAboutCommand();
            $this->offerPublishing();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-mixpanel.php',
            'laravel-mixpanel'
        );

        $this->app->singleton(Mixpanel::class, function (Application $app): Mixpanel {

            $mixpanel = Mixpanel::make();

            if (config('laravel-mixpanel.identity.auto') && $mixpanel->isActive()) {
                // We try to auto-identify the user
                $mixpanel->setUser($app->make(Authenticatable::class))
                    ->identify();
            }

            return $mixpanel;
        });

        $this->app->register(MixpanelEventServiceProvider::class);
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-mixpanel.php' => config_path('laravel-mixpanel.php'),
        ], 'laravel-mixpanel-config');

        $this->publishes([
            __DIR__.'/../database/migrations/add_tracker_column_to_users_table.php.stub' => $this->getMigrationFileName('add_tracker_column_to_users_table.php'),
        ], 'laravel-mixpanel-migrations');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

    /**
     * Register Mixpanel info to artisan:about command
     *
     * @return void
     */
    protected function registerAboutCommand()
    {
        $mpDebug = config('laravel-mixpanel.debug.enabled', false);
        $mp = resolve(Mixpanel::class);

        AboutCommand::add('Laravel Mixpanel Integration', [
            'Active' => fn () => ($mp->isActive() ? '<fg=green;options=bold>' : '<fg=red;options=bold>').$mp::status()->human.'</>',
            'Current State' => fn () => Mixpanel::status($mp)->reason,
            'Debugging' => fn () => ($mpDebug ? '<fg=green;options=bold>ENABLED</>' : '<fg=red;options=bold>DISABLED</>'),
            'Mixpanel Host' => fn () => config('laravel-mixpanel.host') ?? 'DEFAULT',
            'Mixpanel Token' => fn () => filled(config('laravel-mixpanel.token')) ? '<fg=green;options=bold>SET</>' : '<fg=red;options=bold>UNSET</>',
            'Mixpanel Consumer' => fn () => config('laravel-mixpanel.options.consumer'),
            'User Identity Key' => fn () => $mp::$userIdentityKey,
            'User Identity Attributes' => fn () => implode(', ', $mp::$identityAttr ?? []),
        ]);
    }
}

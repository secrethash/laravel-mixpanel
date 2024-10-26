<?php

namespace GemGem\Modules\Mixpanel;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
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
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/mixpanel.php', 'mixpanel');

        $this->app->singleton(Mixpanel::class, function (Application $app): Mixpanel {

            $mixpanel = Mixpanel::make();

            if (config('mixpanel.identity.auto') && $mixpanel->isActive()) {
                // We try to auto-identify the user
                $mixpanel->setUser($app->make(Authenticatable::class))
                    ->identify();
            }

            return $mixpanel;
        });

        $this->app->register(MixpanelEventServiceProvider::class);
    }

    /**
     * Register Mixpanel info to artisan:about command
     *
     * @return void
     */
    protected function registerAboutCommand()
    {
        $mpDebug = config('mixpanel.debug.enabled', false);
        $mp = resolve(Mixpanel::class);

        AboutCommand::add('GemGem Mixpanel Integration', [
            'Active' => fn () => ($mp->isActive() ? '<fg=green;options=bold>' : '<fg=red;options=bold>').$mp::status()->human.'</>',
            'Current State' => fn () => Mixpanel::status($mp)->reason,
            'Debugging' => fn () => ($mpDebug ? '<fg=green;options=bold>ENABLED</>' : '<fg=red;options=bold>DISABLED</>'),
            'Mixpanel Host' => fn () => config('services.mixpanel.host') ?? 'DEFAULT',
            'Mixpanel Token' => fn () => filled(config('services.mixpanel.token')) ? '<fg=green;options=bold>SET</>' : '<fg=red;options=bold>UNSET</>',
            'Mixpanel Consumer' => fn () => config('mixpanel.options.consumer'),
            'User Identity Key' => fn () => $mp::$userIdentityKey,
            'User Identity Attributes' => fn () => implode(', ', $mp::$identityAttr ?? []),
        ]);
    }
}

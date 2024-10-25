<?php

namespace Secrethash\Mixpanel;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Secrethash\Mixpanel\Events\MixpanelEvent;
use Secrethash\Mixpanel\Listeners\MixpanelListener;

class MixpanelEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        MixpanelEvent::class => [
            MixpanelListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}

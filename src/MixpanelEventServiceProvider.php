<?php

namespace GemGem\Modules\Mixpanel;

use Corals\Modules\C2C\Events\CheckoutCompleted;
use Corals\Modules\C2C\Events\ProductViewed;
use Corals\Modules\ShoppingCart\Events\ShoppingCartCreated;
use Corals\Modules\ShoppingCart\Events\ShoppingCartUpdated;
use GemGem\Modules\Mixpanel\Events\AddListingEvent;
use GemGem\Modules\Mixpanel\Events\MakeOfferEvent;
use GemGem\Modules\Mixpanel\Events\MixpanelEvent;
use GemGem\Modules\Mixpanel\Listeners\AddListingListener;
use GemGem\Modules\Mixpanel\Listeners\MakeOfferListener;
use GemGem\Modules\Mixpanel\Listeners\MixpanelListener;
use GemGem\Modules\Mixpanel\Listeners\TrackAddToCart;
use GemGem\Modules\Mixpanel\Listeners\TrackProductViewsMixpanel;
use GemGem\Modules\Mixpanel\Listeners\TrackPurchase;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class MixpanelEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        MixpanelEvent::class => [
            MixpanelListener::class,
        ],
        MakeOfferEvent::class => [
            MakeOfferListener::class,
        ],
        AddListingEvent::class => [
            AddListingListener::class,
        ],
        ProductViewed::class => [
            TrackProductViewsMixpanel::class,
        ],
        ShoppingCartUpdated::class => [
            TrackAddToCart::class,
        ],
        ShoppingCartCreated::class => [
            //? Can cause multiple triggers, kept for reference
            // TrackAddToCart::class,
        ],
        CheckoutCompleted::class => [
            TrackPurchase::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}

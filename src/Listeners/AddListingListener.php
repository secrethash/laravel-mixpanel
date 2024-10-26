<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Events\AddListingEvent;
use GemGem\Modules\Mixpanel\Mixpanel;

class AddListingListener extends BaseTrackingListener
{
    /**
     * Handle the Event Listening
     *
     * @return void
     */
    public function handle(AddListingEvent $event)
    {
        $product = $event->product;

        Mixpanel::track(TrackingEvents::AddListing, [
            'product_id' => $product->id,
            'owner' => [
                'owner_id' => $product->owner?->id,
                'name' => $product->owner?->full_name,
            ],
            'listed_price' => $product->listed_price,
            'listed_price_currency' => $product->listed_price_currency,
            'status' => $product->status,
        ]);
    }
}

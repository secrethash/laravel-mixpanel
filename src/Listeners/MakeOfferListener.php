<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Events\MakeOfferEvent;
use GemGem\Modules\Mixpanel\Mixpanel;

class MakeOfferListener extends BaseTrackingListener
{
    /**
     * Handle the Event Listening
     *
     * @return void
     */
    public function handle(MakeOfferEvent $event)
    {
        $offerHistory = $event->offerHistory;

        Mixpanel::track(TrackingEvents::MakeOffer, [
            'product_id' => $offerHistory->product_id,
            'offer' => [
                'offer_id' => $offerHistory->offer_id,
                'offer_type' => $offerHistory->type,
                'offer_status' => $offerHistory->status,
                'offer_created_by' => $offerHistory->created_by,
                'offer_currency' => $offerHistory->offer->currency_code,
                'last_offer' => $offerHistory->last_offer,
                'buyer_offer' => $offerHistory->buyer_offer,
                'seller_offer' => $offerHistory->seller_offer,
            ],
            'buyer' => [
                'buyer_id' => $offerHistory->buyer?->id,
                'name' => $offerHistory->buyer?->name,
            ],
            'seller' => [
                'seller_id' => $offerHistory->seller?->id,
                'name' => $offerHistory->seller?->name,
            ],
        ]);
    }
}

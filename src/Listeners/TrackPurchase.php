<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use Corals\Modules\C2C\Events\CheckoutCompleted;
use Corals\Modules\Sales\Modules\Promotion\Repository\PromotionRepository;
use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Mixpanel;

class TrackPurchase extends BaseTrackingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(CheckoutCompleted $event)
    {
        foreach ($event->orders as $order) {
            /** @var \Corals\Modules\Sales\Models\Order $order */

            $promotions = PromotionRepository::getPromotionsForOrder($order->id);
            $dataArr = [
                'product_id' => $order->product->id,
                'cart_instance_id' => $event->dto->instanceId,
                'status' => $order->status,
                'promotions' => $promotions,
            ];

            Mixpanel::track(TrackingEvents::Purchase, $dataArr);
        }
    }

}

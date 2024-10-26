<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use Corals\Modules\ShoppingCart\Events\ShoppingCartUpdated;
use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Mixpanel;

class TrackAddToCart extends BaseTrackingListener
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
    public function handle(ShoppingCartUpdated $event)
    {
        // If cart items count == 0, it is not Add to Cart
        if (! $event->cart->getCartItemsCount()) {
            return;
        }

        $data = [
            'story' => $event::STORY,
            'cart_instance' => $event->cart->instance_id,
            'items_count' => $event->cart->getCartItemsCount(),
            'owner' => $event->cart->user?->uuid,
        ];

        $cartItems = $event->cart->cart['items'] ?? [];
        $items = array_map(function ($value) {
            if (is_array($value) && isset($value['data'])) {
                return $value['data'];
            }

        }, $cartItems);
        $data['items'] = $items;

        Mixpanel::track(TrackingEvents::AddToCart, $data);

    }
}

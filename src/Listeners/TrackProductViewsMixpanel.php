<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use Corals\Modules\C2C\DTO\ProductViewsDTO;
use Corals\Modules\C2C\Events\ProductViewed;
use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Mixpanel;
use Illuminate\Support\Facades\Cache;

class TrackProductViewsMixpanel extends BaseTrackingListener
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
    public function handle(ProductViewed $event)
    {
        $uniqueId = $this->generateUniqueId($event->dto);

        // Check if the cache lock exists
        if (Cache::get($uniqueId)) {
            return;
        }

        Cache::put($uniqueId, true);

        try {
            Mixpanel::track(TrackingEvents::ViewItem, $event->dto->toArray());
        } finally {
            Cache::forget($uniqueId);
        }
    }

    /**
     * The unique ID for Listener Handler
     *
     * @return string
     */
    protected function generateUniqueId(ProductViewsDTO $dto)
    {
        $visitor = $dto->user_id ?? $dto->vid ?? $dto->visitor_id;
        $destination = $dto->product_id;

        return implode('_', [
            self::class,
            $visitor,
            $destination,
        ]);
    }
}

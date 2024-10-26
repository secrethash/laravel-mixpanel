<?php

namespace GemGem\Modules\Mixpanel\Events;

use Corals\Modules\C2C\Models\OfferHistory;
use GemGem\Modules\Mixpanel\Contracts\MixpanelEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MakeOfferEvent implements MixpanelEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public OfferHistory $offerHistory
    ) {}
}

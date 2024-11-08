<?php

namespace Secrethash\Mixpanel\Tests\Suite\Events;

use Secrethash\Mixpanel\Contracts\MixpanelEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderSuccessfulEvent implements MixpanelEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
    * Create a new event instance.
    *
    * @return void
    */
    public function __construct(
        public array $order
    ) {
    }
}

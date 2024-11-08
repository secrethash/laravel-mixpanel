<?php

namespace Secrethash\Mixpanel\Tests\Suite\Listeners;

use Secrethash\Mixpanel\Tests\Suite\Enums\TrackingEvents;
use Secrethash\Mixpanel\Tests\Suite\Events\OrderSuccessfulEvent;
use Secrethash\Mixpanel\Mixpanel;
use Secrethash\Mixpanel\Listeners\BaseTrackingListener;

class OrderSuccessTrackingListener extends BaseTrackingListener
{
    /**
    * Handle the Event Listening
    *
    * @return void
    */
    public function handle(OrderSuccessfulEvent $event)
    {
        Mixpanel::track(TrackingEvents::OrderSuccessful, $event->order);
    }
}

<?php

use Illuminate\Support\Facades\Event;
use Secrethash\Mixpanel\Events\MixpanelEvent;
use Secrethash\Mixpanel\Mixpanel;
use Secrethash\Mixpanel\Tests\Suite\Enums\TrackingEvents;
use Secrethash\Mixpanel\Tests\Suite\Events\OrderSuccessfulEvent;

it('can be disabled during runtime', function () {
    Mixpanel::$track = false;
    $this->assertFalse(resolve(Mixpanel::class)->isActive());
})->after(function () {
    Mixpanel::$track = true;
});

it('can verify tracking is active', function () {
    $this->assertTrue(resolve(Mixpanel::class)->isActive());
});

it('can track fired events', function () {
    Event::fake([MixpanelEvent::class]);

    $order = [
        'order_id' => 123,
        'seller' => [
            'id' => 456,
            'name' => 'Test Seller',
        ],
        'amount' => 256.20,
        'currency' => 'USD',
        'status' => 'created',
    ];

    Mixpanel::track(TrackingEvents::OrderSuccessful, $order);
    Event::assertDispatched(MixpanelEvent::class);

});

it('can track dispatched events', function () {
    Event::fake([OrderSuccessfulEvent::class]);

    $order = [
        'order_id' => 123,
        'seller' => [
            'id' => 456,
            'name' => 'Test Seller',
        ],
        'amount' => 256.20,
        'currency' => 'USD',
        'status' => 'created',
    ];

    OrderSuccessfulEvent::dispatch($order);
    Event::assertDispatched(OrderSuccessfulEvent::class);

});

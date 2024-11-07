# Mixpanel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/secrethash/laravel-mixpanel.svg?style=flat-square)](https://packagist.org/packages/secrethash/laravel-mixpanel)
[![Tests](https://img.shields.io/github/actions/workflow/status/secrethash/laravel-mixpanel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/secrethash/laravel-mixpanel/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/secrethash/laravel-mixpanel.svg?style=flat-square)](https://packagist.org/packages/secrethash/laravel-mixpanel)

This package provides a sane bridge between Laravel applications and Mixpanel PHP SDK.

## Installation

You can install the package via composer:

```bash
composer require secrethash/laravel-mixpanel
```

Publish the Migrations and Config:

```bash
php artisan vendor:publish --provider="Secrethash\Mixpanel\MixpanelServiceProvider"
```

## Idealogies

### Events & Listeners

We use Laravel's event and listeners to track and send data to Mixpanel. This makes it easier to hook into events as and when needed.

Although the package does not force to use events and listeners in your application while tracking, but it's still recommended for maintainability. Similarly we will also take the example of events and listeners in the examples below.

### Events Naming Consistency

We enforce a validation to make sure all the events sent are an instance of the class set in config key `laravel-mixpanel.tracker.events`. This should be a string backed enum.  This is done to avoid event name inconsistency on mixpanel eg: `User Registered` and `User Created`

### User Auto-identification

Enabled by default, we try early identification of the user using a user tracker (uuid). Every user is given a unique uuid which is saved in the database column defined in the config key `laravel-mixpanel.tracker.database_column`.

## Usage

1. Tracking an event is as easy as:

```php
use Secrethash\Mixpanel\Mixpanel;
use Secrethash\Mixpanel\Enums\TrackingEvents;

$data = [
    'registration_status' => 'success',
    'account_status' => 'verified',
];

Mixpanel::track(TrackingEvents::userRegistered, $data);
```

## Examples

### Tracking Order Successful Event

- Order event and listener

```php
<?php

namespace App\Events;

use App\Models\Order;
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
        public Order $order
    ) {
    }
}
```

```php
<?php

namespace App\Listeners;

use App\Enums\TrackingEvents;
use App\Events\OrderSuccessfulEvent;
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
        $order = $event->order;

        Mixpanel::track(TrackingEvents::OrderSuccessful, [
            'order_id' => $order->id,
            'seller' => [
                'id' => $order->product->seller?->id,
                'name' => $order->product->seller?->full_name,
            ],
            'amount' => $order->amount,
            'currency' => $order->currency,
            'status' => $order->status,
        ]);
    }
}

```

- Register the event and listener by adding the mapping to `App\Providers\EventServiceProvider::$listen`

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use App\Events\OrderSuccessfulEvent;
use App\Listeners\OrderSuccessTrackingListener;

class EventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ...
        OrderSuccessfulEvent::class => [
            OrderSuccessTrackingListener::class,
        ],
    ];
    ...
}

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Shashwat Mishra](https://github.com/secrethash)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

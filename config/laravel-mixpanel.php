<?php

use Secrethash\Mixpanel\Consumers\DebugConsumer;
use Secrethash\Mixpanel\Consumers\DebugCurlConsumer;
use Secrethash\Mixpanel\Consumers\DebugFileConsumer;
use Secrethash\Mixpanel\Consumers\DebugSocketConsumer;

return [

    /**
     * Mixpanel Connector Configurations
     * -----------------------------------------------------------------------------
     * Specify Mixpanel connector configurations
     * `token` => Mixpanel Project Token
     * `host` => Mixpanel Host (use EU for GDPR compliance, defaults to US)
     */
    'token' => env('MIXPANEL_TOKEN'),
    'host' => env('MIXPANEL_HOST'),

    /**
     * Mixpanel Event Tracking
     * -----------------------------------------------------------------------------
     * `track` => Enable or Disable Mixpanel Tracking by setting
     * this to `true` or `false`
     * -----------------------------------------------------------------------------
     * `tracker.events` => set to an enum object to fail-fast. Tracker events are
     *  a set of events to maintain consistency for `event_name` sent to mixpanel
     */
    'track' => env('MIXPANEL_TRACK', false),

    'tracker' => [
        'database_column' => 'mixpanel_tracker',
        'events' => \Secrethash\Mixpanel\Enums\TrackingEvents::class,
    ],

    /**
     * Mixpanel instantiate Options
     * -----------------------------------------------------------------------------
     * Options sent to Mixpanel during instantiation
     * [consumer] => [socket, curl, file]
     * ? consumer can also be set to `dry` when debugging is enabled.
     */
    'options' => [
        'max_batch_size' => env('MIXPANEL_BATCH_SIZE', 50),
        'consumer' => env('MIXPANEL_CONSUMER', 'socket'),
    ],

    /**
     * Debug Mixpanel
     * -----------------------------------------------------------------------------
     * Enable/Disable debugging inside application or in console
     * ? Note: to debug in queues, `debug.in_console` might need to be `true`
     *
     * [consumers]
     * List of consumer strategies that are used when debug is `true`.
     * A Consumer is selected according to the `options.consumer` value
     * `dry` consumer is for dry run and will not send any data
     */
    'debug' => [
        'enabled' => env('MIXPANEL_DEBUG', false),
        'in_console' => env('MIXPANEL_DEBUG_CONSOLE', false),
        'consumers' => [
            'dry' => DebugConsumer::class,
            'socket' => DebugSocketConsumer::class,
            'curl' => DebugCurlConsumer::class,
            'file' => DebugFileConsumer::class,
        ],
    ],

    /**
     * Mixpanel Identity related configuration
     * -----------------------------------------------------------------------------
     */
    'identity' => [

        /**
         * Auto-identification
         * -----------------------------------------------------------------------------
         * Try to identify the user automatically
         */
        'auto' => env('MIXPANEL_AUTO_IDENTIFY', true),

    ],

    /**
     * Monitoring
     * -----------------------------------------------------------------------------
     * [monitoring > horizon > tags]
     * - Specify tags for Monitoring in Horizon
     * - These can be used to create a monitor in Horizon for the tracking jobs
     */
    'monitoring' => [
        'horizon' => [
            'tags' => [
                'mixpanel-tracking',
            ],
        ],
    ],
];

<?php

namespace Secrethash\Mixpanel\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Secrethash\Mixpanel\Consumers\DebugConsumer;
use Secrethash\Mixpanel\Consumers\DebugCurlConsumer;
use Secrethash\Mixpanel\Consumers\DebugFileConsumer;
use Secrethash\Mixpanel\Consumers\DebugSocketConsumer;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * This method is called before each test.
     *
     * @codeCoverageIgnore
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupMixpanel();
    }

    /**
     * Setup Mixpanel for each tests
     *
     * @codeCoverageIgnore
     */
    protected function setupMixpanel()
    {
        config()->set('laravel-mixpanel.track', true);
        config()->set('laravel-mixpanel.token', 'xyz123abc456');
        config()->set('laravel-mixpanel.tracker', [
            'database_column' => 'mixpanel_tracker',
            'events' => \Secrethash\Mixpanel\Tests\Suite\Enums\TrackingEvents::class,
        ]);
        config()->set('laravel-mixpanel.options', [
            'max_batch_size' => 1,
            'consumer' => 'dry',
        ]);
        config()->set('laravel-mixpanel.debug', [
            'enabled' => true,
            'in_console' => false,
            'consumers' => [
                'dry' => DebugConsumer::class,
                'socket' => DebugSocketConsumer::class,
                'curl' => DebugCurlConsumer::class,
                'file' => DebugFileConsumer::class,
            ],
        ]);
    }
}

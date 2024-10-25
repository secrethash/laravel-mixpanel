<?php

namespace Secrethash\Mixpanel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Secrethash\Mixpanel\Contracts\MixpanelListener as ListenerInterface;
use Secrethash\Mixpanel\Events\MixpanelEvent;
use Secrethash\Mixpanel\Mixpanel;
use Secrethash\Mixpanel\Tracker;

class MixpanelListener implements ListenerInterface, ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected Mixpanel $mixpanel
    ) {}

    /**
     * Handle the Event Listening
     *
     * @return void
     */
    public function handle(MixpanelEvent $event)
    {
        try {
            $tracker = Tracker::make($event->event->value);
            $tracker->track($event->properties, mixpanel: $this->mixpanel);

            if (config('laravel-mixpanel.debug.enabled', false)) {
                Log::debug(
                    "Mixpanel Tracking completed for {$event->event->value}.",
                    [
                        'tracking' => $tracker->getStatus()->toArray(),
                        'event' => $event->event,
                        'properties' => $event->properties,
                        'user_uuid' => $this->mixpanel->getIdentified(),
                    ]
                );
            }
        } catch (\Exception $exception) {
            Log::error("Mixpanel tracking failed for {$event->event->value}: ".$exception->getMessage(), [
                'event' => $event->event,
                'properties' => $event->properties,
                'user_uuid' => $this->mixpanel->getIdentified(),
            ]);
        }

    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = config('laravel-mixpanel.monitoring.horizon.tags', []);

        if (! is_array($tags)) {
            return [];
        }

        return $tags;
    }
}

<?php

namespace Secrethash\Mixpanel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Secrethash\Mixpanel\Contracts\MixpanelListener;

abstract class BaseTrackingListener implements MixpanelListener, ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public $afterCommit = true;

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

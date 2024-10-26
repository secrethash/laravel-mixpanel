<?php

namespace GemGem\Modules\Mixpanel\Listeners;

use GemGem\Modules\Mixpanel\Contracts\MixpanelListener;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseTrackingListener implements MixpanelListener, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $afterCommit = true;

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = config('mixpanel.monitoring.horizon.tags', []);

        if (! is_array($tags)) {
            return [];
        }

        return $tags;
    }
}

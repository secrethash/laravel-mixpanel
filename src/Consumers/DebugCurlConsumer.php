<?php

namespace GemGem\Modules\Mixpanel\Consumers;

use ConsumerStrategies_CurlConsumer;
use Illuminate\Support\Facades\Log;

class DebugCurlConsumer extends ConsumerStrategies_CurlConsumer
{
    public function persist($batch)
    {
        if (isset($batch[0]['event']) && $batch[0]['event'] == 'force_error') {
            Log::debug('Error Occurred: Mixpanel Debug consumer', $batch);
        } else {
            Log::debug('Mixpanel DEBUG (Curl Consumer): ', $batch);
        }

        return parent::persist($batch);
    }
}

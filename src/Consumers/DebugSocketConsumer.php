<?php

namespace GemGem\Modules\Mixpanel\Consumers;

use ConsumerStrategies_SocketConsumer;
use Illuminate\Support\Facades\Log;

class DebugSocketConsumer extends ConsumerStrategies_SocketConsumer
{
    public function persist($batch)
    {
        if (isset($batch[0]['event']) && $batch[0]['event'] == 'force_error') {
            Log::debug('Error Occurred: Mixpanel Debug consumer', $batch);
        } else {
            Log::debug('Mixpanel DEBUG (Socket Consumer): ', $batch);
        }

        return parent::persist($batch);
    }
}

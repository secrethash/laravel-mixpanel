<?php

namespace Secrethash\Mixpanel\Consumers;

use ConsumerStrategies_FileConsumer;
use Illuminate\Support\Facades\Log;

class DebugFileConsumer extends ConsumerStrategies_FileConsumer
{
    public function persist($batch)
    {
        if (isset($batch[0]['event']) && $batch[0]['event'] == 'force_error') {
            Log::debug('Error Occurred: Mixpanel Debug consumer', $batch);
        } else {
            Log::debug('Mixpanel DEBUG (File Consumer): ', $batch);
        }

        return parent::persist($batch);
    }
}

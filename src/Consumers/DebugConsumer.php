<?php

namespace GemGem\Modules\Mixpanel\Consumers;

use ConsumerStrategies_AbstractConsumer;
use Illuminate\Support\Facades\Log;

class DebugConsumer extends ConsumerStrategies_AbstractConsumer
{
    public function persist($batch)
    {
        if (isset($batch[0]['event']) && $batch[0]['event'] == 'force_error') {
            Log::debug('Error Occurred: Mixpanel Debug consumer', $batch);
            $this->_handleError(0, '');

            return false;
        } else {
            Log::debug('Mixpanel DEBUG (Dry Consumer): ', $batch);

            return true;
        }
    }
}

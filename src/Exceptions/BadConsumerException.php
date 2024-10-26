<?php

namespace GemGem\Modules\Mixpanel\Exceptions;

use Exception;

class BadConsumerException extends Exception
{
    public function __construct(string $consumer, array $available, ?\Throwable $previous = null)
    {
        $consumerList = implode(', ', $available);
        $message = "Invalid Consumer [{$consumer}] called for Mixpanel Tracking. Available values are: [{$consumerList}]";

        parent::__construct($message, 500, $previous);
    }
}

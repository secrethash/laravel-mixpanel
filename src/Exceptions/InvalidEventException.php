<?php

namespace GemGem\Modules\Mixpanel\Exceptions;

class InvalidEventException extends \Exception
{
    public function __construct()
    {
        $message = 'Invalid event (non-trackable) name provided.';
        parent::__construct($message);
    }
}

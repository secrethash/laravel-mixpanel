<?php

namespace Secrethash\Mixpanel\Exceptions;

class InvalidEventException extends \Exception
{
    public function __construct()
    {
        $events = config('laravel-mixpanel.tracker.events');
        $message = "Invalid event (non-trackable) name provided. The event is either empty/null or not an instanceof [$events]";
        parent::__construct($message);
    }
}

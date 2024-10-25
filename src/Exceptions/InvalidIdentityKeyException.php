<?php

namespace Secrethash\Mixpanel\Exceptions;

class InvalidIdentityKeyException extends \Exception
{
    public function __construct(string $type = 'identity', string|int|null $key = null)
    {

        $message = "[{$type}]";
        $message .= $key ? " ({$key})" : '';
        parent::__construct(
            "Key for {$message} provided has an Invalid Format. The {$type} key should have a valid UUID v4 format."
        );
    }
}

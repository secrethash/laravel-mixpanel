<?php

namespace Secrethash\Mixpanel\Exceptions;

class SetupMissingException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            '[Mixpanel Instance Invalid] Mixpanel setup is missing or incomplete. Please ensure all required configurations are set.'
        );
    }
}

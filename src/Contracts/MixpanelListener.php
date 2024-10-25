<?php

namespace Secrethash\Mixpanel\Contracts;

interface MixpanelListener
{
    public function tags(): array;
}

<?php

namespace GemGem\Modules\Mixpanel\Contracts;

interface MixpanelListener
{
    public function tags(): array;
}

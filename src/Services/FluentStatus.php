<?php

namespace GemGem\Modules\Mixpanel\Services;

use Illuminate\Support\Fluent;

/**
 * Fluent Statuses
 *
 * @property bool $state
 * @property string $human
 * @property string $reason
 */
class FluentStatus extends Fluent
{
    public function __construct(bool $state, string $human, string $reason)
    {
        parent::__construct([
            'state' => $state,
            'human' => $human,
            'reason' => $reason,
        ]);
    }
}

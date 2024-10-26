<?php

namespace GemGem\Modules\Mixpanel;

use Browser;
use GemGem\Modules\Mixpanel\Exceptions\InvalidEventException;
use GemGem\Modules\Mixpanel\Services\FluentStatus;
use Illuminate\Http\Request;

class Tracker
{
    /**
     * Tracking status
     */
    protected bool $status;

    /**
     * Tracking status message
     */
    protected string $message;

    public function __construct(
        protected string $event
    ) {
    }

    /**
     * Make new Tracker instance
     */
    public static function make(string $event): self
    {
        return new self($event);
    }

    /**
     * Initiate Mixpanel tracking
     *
     * @throws InvalidEventException
     */
    public function track(array $properties, ?string $event = null, ?Mixpanel $mixpanel = null): self
    {
        $service = $mixpanel ?? resolve(Mixpanel::class);
        $mp = $service->getInstance();

        $this->event = filled($event) ? $event : $this->event;

        if (blank($this->event)) {
            throw new InvalidEventException();
        }

        if (! $service->isActive()) {
            $this->status = false;
            $this->message = 'Mixpanel is Inactive';

            return $this;
        }

        $properties['distinct_id'] = $service->getIdentified();

        $defaultProperties = $this->getDefaultProperties();

        $mp->track(
            $this->event,
            array_merge(
                $defaultProperties,
                $properties
            )
        );

        $this->status = true;
        $this->message = 'Event was successfully sent to be tracked.';

        return $this;
    }

    /**
     * Fetch tracking status
     */
    public function wasTracked(): bool
    {
        return $this->status ?? false;
    }

    /**
     * Fetch Fluent tracking status
     */
    public function getStatus(): FluentStatus
    {
        return new FluentStatus(
            $this->wasTracked(),
            $this->wasTracked() ? 'Tracked' : 'Failed',
            $this->message,
        );
    }

    /**
     * Get default tracking properties
     */
    private function getDefaultProperties(): array
    {
        $request = resolve(Request::class);
        $browserVersion = Browser::browserName();
        $browserVersion = blank($browserVersion) ? (Browser::isBot() ? 'Robot' : '') : $browserVersion;
        $hardwareVersion = Browser::deviceType();
        $osVersion = Browser::platformName();

        $data = [
            'Url' => $request->getUri(),
            'Operating System' => $osVersion,
            'Hardware' => $hardwareVersion,
            '$browser' => $browserVersion,
            'Referrer' => $request->header('referer'),
            '$referring_domain' => ($request->header('referer')
                ? parse_url($request->header('referer'))['host']
                : null),
            'ip' => $request->ip(),
        ];

        return $data;
    }
}

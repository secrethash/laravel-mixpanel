<?php

namespace GemGem\Modules\Mixpanel;

use Corals\User\Models\User;
use GemGem\Modules\Mixpanel\Enums\TrackingEvents;
use GemGem\Modules\Mixpanel\Events\MixpanelEvent;
use GemGem\Modules\Mixpanel\Exceptions\BadConsumerException;
use GemGem\Modules\Mixpanel\Exceptions\InvalidIdentityKeyException;
use GemGem\Modules\Mixpanel\Exceptions\SetupMissingException;
use GemGem\Modules\Mixpanel\Services\FluentStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mixpanel as MixpanelPHP;

class Mixpanel
{
    /**
     * Tracked User
     */
    protected Authenticatable|User|null $user = null;

    /**
     * Tracking UUID
     */
    protected string $uuid;

    /**
     * Toggle Mixpanel Tracking
     */
    public static bool $track;

    /**
     * Anonymous User Tracking IDs
     */
    protected array $anonymous_ids = [];

    /**
     * User Identity to send to Mixpanel
     * (User Model Attributes)
     */
    public static array $identityAttr = [
        'name',
        'last_name',
        'username',
    ];

    /**
     * Key where the UUID is set
     *
     * This can also be overwritten programmatically
     * in App\Providers\AppServiceProvider or any
     * other service provider as such
     * Mixpanel::$userIdentityKey = 'tracking_id'
     */
    public static string $userIdentityKey = 'uuid';

    /**
     * Mixpanel Instance
     */
    protected MixpanelPHP $mixpanel;

    /**
     * Mixpanel API Token
     */
    protected ?string $apiToken;

    /**
     * Mixpanel API Host
     */
    protected ?string $apiHost;

    public function __construct(
        private Request $request,
    ) {
        if (isset(self::$track)) {
            self::$track = self::$track && config('mixpanel.track');
        } else {
            self::$track = config('mixpanel.track');
        }
        $this->setup();
    }

    /**
     * Statically make Mixpanel Instance
     */
    public static function make(): self
    {
        return new self(resolve(Request::class));
    }

    /**
     * Setup the Mixpanel Credentials
     */
    public function credentials(string $token, ?string $host = null): self
    {
        $this->apiToken = $token;
        $this->apiHost = $host;

        return $this;
    }

    /**
     * Sets the User that will be sent to Mixpanel
     */
    public function setUser(?Authenticatable $user = null): self
    {
        $this->user = $user ?? $this->user ?? $this->request->user();

        if ($this->user && config('mixpanel.identity.auto')) {
            $this->autoIdentify();
        }

        return $this;
    }

    /**
     * Setup Mixpanel
     */
    public function setup(): self
    {
        $token = config('services.mixpanel.token') ?? '';
        $options = config('mixpanel.options');
        $debug = config('mixpanel.debug');

        $this->setUser();
        $this->credentials($token);

        if (filled($this->apiHost)) {
            $options['host'] = $this->apiHost;
        }

        if (isset($debug['enabled']) && $debug['enabled']) {
            $options['debug'] = true;

            if (isset($debug['in_console']) && ! $debug['in_console'] && app()->runningInConsole()) {
                // remove mixpanel sdk's debug messages in artisan commands
                $options['debug'] = false;
            }

            $options['consumers']['custom-debugger'] = $this->getDebugConsumer();
            $options['consumer'] = 'custom-debugger';
        }

        $this->mixpanel = MixpanelPHP::getInstance(
            $this->apiToken,
            $options
        );

        return $this;
    }

    /**
     * Identify Tracked User's Identity
     */
    public function identify(): self
    {
        if (! $this->mixpanel) {
            throw new SetupMissingException;
        }

        if (config('mixpanel.identity.auto')) {
            $this->autoIdentify();
        }

        $identified = $this->getIdentified();

        if ($this->user) {
            if ($identified !== $this->user->{self::$userIdentityKey}) {
                // We use the uuid as Anonymous User ID to merge the metrics
                $this->mixpanel->identify($this->user->{self::$userIdentityKey}, $identified);
            }

            $this->mixpanel->people->set($identified, $this->user->only(self::$identityAttr));
            $this->registerAnonymousIds();
        }

        return $this;
    }

    /**
     * Get Identified User $identifier Value
     */
    public function getIdentified(): string|int
    {
        return $this->user?->{self::$userIdentityKey} ?? $this->uuid;
    }

    protected function registerAnonymousIds(): bool
    {
        $this->verifyIdentityKeys($this->uuid);
        $this->verifyAnonymousKeys(...array_filter($this->anonymous_ids));

        foreach ($this->anonymous_ids as $anon) {
            $this->mixpanel->identify($this->uuid, $anon);
        }

        return true;
    }

    /**
     * Get the Mixpanel Instance
     */
    public function getInstance(): MixpanelPHP
    {
        if (! isset($this->mixpanel)) {
            $this->setup();
        }

        return $this->mixpanel;
    }

    /**
     * Auto-identify User for tracking
     */
    public function autoIdentify(): self
    {
        // UUID is not set, is blank or not UUIDv4
        if (
            ! isset($this->uuid) ||
            blank($this->uuid) ||
            ! Str::isUuid($this->uuid)
        ) {
            $this->uuid = $this->user?->{self::$userIdentityKey} ?? Str::uuid();
        }

        // User's Identity key is empty
        if ($this->user && ! $this->user->{self::$userIdentityKey}) {
            $this->verifyIdentityKeys($this->uuid);
            $this->user->{self::$userIdentityKey} = $this->uuid;
            $this->user->saveQuietly();
        }

        $this->verifyIdentityKeys($this->uuid, $this->user?->{self::$userIdentityKey});

        // uuid != user's identity key
        if ($this->user && $this->uuid !== $this->user->{self::$userIdentityKey}) {
            // we prevent duplications here by [['uuid'] => 'uuid']
            $this->anonymous_ids[$this->uuid] = $this->uuid;
            $this->uuid = $this->user->{self::$userIdentityKey};
        }

        return $this;
    }

    /**
     * Verify Identity Keys
     *
     * @param  array<string>  ...$keys
     */
    protected function verifyIdentityKeys(...$keys): bool
    {
        foreach ($keys as $key) {
            // skip null keys
            if (is_null($key)) {
                continue;
            }

            if (! Str::isUuid($key)) {
                throw new InvalidIdentityKeyException(key: $key);
            }
        }

        return true;
    }

    /**
     * Verify Anonymous Keys
     *
     * @param  array<string>  ...$keys
     */
    protected function verifyAnonymousKeys(...$keys): bool
    {
        foreach ($keys as $key) {
            if (! Str::isUuid($key)) {
                throw new InvalidIdentityKeyException('anonymous', $key);
            }
        }

        return true;
    }

    /**
     * Check if mixpanel is Active
     */
    public function isActive(): bool
    {
        if (filled(config('services.mixpanel.token'))) {
            return self::$track;
        }

        return false;
    }

    /**
     * Check if mixpanel is Active
     */
    public static function status(?Mixpanel $mixpanel = null): FluentStatus
    {
        $mp = $mixpanel ?? resolve(self::class);
        $active = $mp->isActive();
        $reason = $active ? 'Mixpanel is active' : '';

        if (! $active) {
            if (! config('mixpanel.track')) {
                $reason = 'Tracking Disabled in Configuration';
            } elseif (blank(config('services.mixpanel.token'))) {
                $reason = 'Tracking Disabled due to missing Token.';
            } elseif (! $mp::$track) {
                $reason = 'Tracking Disabled explicitly during runtime.';
            } else {
                $reason = 'Tracking Disabled. Reason Unknown.';
            }
        }

        return new FluentStatus(
            $active,
            $active ? 'ACTIVE' : 'INACTIVE',
            $reason
        );
    }

    /**
     * Track Event and attach to the User
     */
    public static function track(TrackingEvents $event, array $properties)
    {
        // return Tracker::make($event->value)->track($properties);
        return event(new MixpanelEvent($event, $properties));
    }

    /**
     * Get the consumer for debugging
     */
    private function getDebugConsumer(): string
    {
        $consumer = config('mixpanel.options.consumer');
        $debuggers = config('mixpanel.debug.consumers', []);

        if (isset($debuggers[$consumer]) && ! empty($debuggers[$consumer])) {
            return $debuggers[$consumer];
        }

        throw new BadConsumerException($consumer, array_keys($debuggers));
    }
}

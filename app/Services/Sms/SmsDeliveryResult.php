<?php

namespace App\Services\Sms;

/**
 * What a gateway said about one message.
 *
 * A value object rather than a loose array so callers cannot quietly misread
 * a failure as a success by checking a key that a particular provider never
 * happened to set.
 */
final class SmsDeliveryResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $provider,
        public readonly ?string $messageId = null,
        public readonly ?string $cost = null,
        public readonly ?string $error = null,
    ) {}

    public static function sent(string $provider, ?string $messageId = null, ?string $cost = null): self
    {
        return new self(true, $provider, $messageId, $cost);
    }

    public static function failed(string $provider, string $error): self
    {
        return new self(false, $provider, error: $error);
    }

    /**
     * Legacy array shape.
     *
     * SmsService's public API predates this class and is consumed by
     * AuthController, OtpService and the advisory jobs; keeping the shape
     * identical means the provider refactor changes nothing above it.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'gateway' => $this->provider,
            'message_id' => $this->messageId,
            'cost' => $this->cost,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}

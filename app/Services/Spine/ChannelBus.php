<?php

namespace App\Services\Spine;

use App\Models\DeliveryReceipt;
use App\Models\MessageSuppressionList;
use App\Models\OutboundMessage;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class ChannelBus
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Dispatch an outbound message across one or more channel drivers.
     */
    public function send(OutboundMessage $message, array $audience = [], array $channelDrivers = ['push', 'sms']): OutboundMessage
    {
        $message->audience_filter = $audience;
        $message->status = 'processing';
        $message->save();

        $recipientsCount = 0;

        foreach ($channelDrivers as $driver) {
            try {
                $count = $this->dispatchToDriver($message, $driver, $audience);
                $recipientsCount += $count;
            } catch (\Throwable $e) {
                Log::error("ChannelBus dispatch error [{$driver}]: ".$e->getMessage());
                $message->error_message = $e->getMessage();
            }
        }

        $message->status = 'sent';
        $message->sent_at = now();
        $message->recipient_count = $recipientsCount;
        $message->save();

        return $message;
    }

    protected function dispatchToDriver(OutboundMessage $message, string $driver, array $audience): int
    {
        $payload = $message->payload;
        $title = $payload['title'] ?? 'Mkulima Forum Advisory';
        $body = $payload['body'] ?? '';

        // Handle SMS driver using SmsService
        if ($driver === 'sms') {
            $phone = $audience['phone'] ?? null;
            if ($phone) {
                if ($this->isSuppressed($phone, 'sms')) {
                    Log::info("ChannelBus: Suppressed SMS to {$phone}");

                    return 0;
                }
                $this->smsService->sendSms($phone, "{$title}: {$body}");
                $this->recordReceipt($message, $phone, 'sms', 'delivered');

                return 1;
            }
        }

        // Handle in-app banner / push / whatsapp mock adapters
        if (in_app_driver($driver)) {
            $this->recordReceipt($message, $audience['user_id'] ?? null, $driver, 'delivered');

            return 1;
        }

        return 0;
    }

    protected function isSuppressed(string $identifier, string $driver): bool
    {
        return MessageSuppressionList::where('recipient_identifier', $identifier)
            ->where('channel_driver', $driver)
            ->exists();
    }

    protected function recordReceipt(OutboundMessage $message, mixed $identifier, string $driver, string $status): void
    {
        DeliveryReceipt::create([
            'message_id' => $message->id,
            'recipient_identifier' => is_string($identifier) ? $identifier : null,
            'user_id' => is_numeric($identifier) ? $identifier : null,
            'channel_driver' => $driver,
            'status' => $status,
            'delivered_at' => now(),
        ]);
    }
}

function in_app_driver(string $driver): bool
{
    return in_array($driver, ['push', 'whatsapp_business', 'whatsapp_channel_link', 'telegram', 'email', 'in_app_banner', 'ussd_session']);
}

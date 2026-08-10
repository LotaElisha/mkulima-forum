<?php

namespace App\Services\Verify;

use App\Models\Agrodealer;
use App\Models\OutboundMessage;
use App\Services\Spine\AuditTrail;
use App\Services\Spine\ChannelBus;

class AgrodealerKycService
{
    protected ChannelBus $channelBus;
    protected AuditTrail $auditTrail;

    public function __construct(ChannelBus $channelBus, AuditTrail $auditTrail)
    {
        $this->channelBus = $channelBus;
        $this->auditTrail = $auditTrail;
    }

    public function updateStatus(Agrodealer $dealer, string $newStatus, ?string $notes = null, ?int $reviewerId = null): Agrodealer
    {
        $before = ['status' => $dealer->status];

        $dealer->status = $newStatus;
        if (in_array($newStatus, ['MKULIMA_VERIFIED', 'REGULATOR_RECORD_MATCHED'])) {
            $dealer->verified_at = now();
        }
        $dealer->save();

        $this->auditTrail->record($dealer, 'status_changed', $before, ['status' => $newStatus, 'notes' => $notes], null);

        // Part C: Send notification via ChannelBus if user linked
        if ($dealer->user_id && $dealer->user?->phone) {
            $msg = new OutboundMessage([
                'channel_driver' => 'sms',
                'payload' => [
                    'title' => 'Mkulima Forum Verification',
                    'body' => "Hali ya wakala wako imebadilishwa kuwa: {$newStatus}",
                ],
            ]);
            $this->channelBus->send($msg, ['phone' => $dealer->user->phone], ['sms']);
        }

        return $dealer;
    }

    public function checkExpiries(): int
    {
        $expiredDealers = Agrodealer::where('licence_expiry', '<', now()->toDateString())
            ->whereNotIn('status', ['EXPIRED', 'SUSPENDED'])
            ->get();

        $count = 0;
        foreach ($expiredDealers as $dealer) {
            $this->updateStatus($dealer, 'EXPIRED', 'Automatic licence expiry downgrade');
            $count++;
        }

        return $count;
    }
}

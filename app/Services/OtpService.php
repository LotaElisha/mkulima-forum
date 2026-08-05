<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OtpService
{
    public function generate(string $phone, string $purpose = 'login'): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]);

        // Rate limit: max 3 OTPs per phone per hour
        $key = 'otp_rate_limit:' . $phone;
        $attempts = Cache::increment($key);
        if ($attempts === 1) {
            Cache::put($key, 1, now()->addHour());
        }

        return [
            'code' => $code,
            'expires_in' => 600,
            'message' => 'OTP sent successfully',
        ];
    }

    public function verify(string $phone, string $code, string $purpose = 'login'): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['used_at' => now()]);
        return true;
    }

    public function isRateLimited(string $phone): bool
    {
        $attempts = Cache::get('otp_rate_limit:' . $phone, 0);
        return $attempts >= 3;
    }

    public function sendSms(string $phone, string $message): bool
    {
        // Africa's Talking integration placeholder
        // TODO: Implement actual SMS sending
        \Log::info("SMS to {$phone}: {$message}");
        return true;
    }
}

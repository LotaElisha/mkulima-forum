<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function generate(string $phone, string $purpose = 'login'): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]);

        // Rate limit: max 3 OTPs per phone per hour
        $key = 'otp_rate_limit:'.$phone;
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
        $attemptKey = "otp_verify_attempts:{$purpose}:{$phone}";
        if ((int) Cache::get($attemptKey, 0) >= 5) {
            return false;
        }

        $otp = OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->latest('id')
            ->get()
            ->first(fn (OtpCode $candidate) => Hash::check($code, $candidate->code));

        if (! $otp) {
            $attempts = Cache::increment($attemptKey);
            if ($attempts === 1) {
                Cache::put($attemptKey, 1, now()->addMinutes(15));
            }

            return false;
        }

        $otp->update(['used_at' => now()]);
        Cache::forget($attemptKey);

        return true;
    }

    public function isVerificationLimited(string $phone, string $purpose = 'login'): bool
    {
        return (int) Cache::get("otp_verify_attempts:{$purpose}:{$phone}", 0) >= 5;
    }

    public function isRateLimited(string $phone): bool
    {
        $attempts = Cache::get('otp_rate_limit:'.$phone, 0);

        return $attempts >= 3;
    }
}

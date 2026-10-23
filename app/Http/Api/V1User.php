<?php

namespace App\Http\Api;

use App\Models\User;
use App\Services\Seller\SellerStatus;

/**
 * Account shape for /api/v1. UUIDs only — numeric ids never leave the server.
 * Keys match the Flutter User model (camelCase) so the client does not keep
 * a second parser.
 */
final class V1User
{
    public static function from(User $user): array
    {
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'emailVerified' => $user->hasVerifiedEmail(),
            'role' => $user->role,
            'kycStatus' => $user->kyc_status,
            'preferredLanguage' => $user->preferred_language ?? 'sw',
            'avatar' => $user->avatar,
            'createdAt' => $user->created_at?->toIso8601String(),
            'seller' => app(SellerStatus::class)->payload($user),
        ];
    }
}

<?php

namespace App\Services\Seller;

use App\Models\SellerApplication;
use App\Models\User;
use App\Support\Roles;

/**
 * The single answer to "may this person sell, and if not, where are they?"
 *
 * Both tiers ask this. The app asks it to decide what to draw; the API asks it
 * to decide what to allow. When those two disagree you get the bug this was
 * written for: the profile screen offered every farmer a Seller Dashboard and
 * the endpoint answered 403 with nothing a farmer could act on.
 */
class SellerStatus
{
    public const NONE = 'none';          // never applied

    public const PENDING = 'pending';    // applied, awaiting review

    public const REJECTED = 'rejected';  // refused, may apply again

    public const APPROVED = 'approved';  // may sell

    /**
     * Whether this account may use seller endpoints.
     *
     * Role is the authority, not the application row. An agrodealer created by
     * an administrator never filled in an application and must still be able
     * to sell.
     */
    public function canSell(User $user): bool
    {
        return in_array($user->role, Roles::SELLERS, true);
    }

    public function state(User $user): string
    {
        if ($this->canSell($user)) {
            return self::APPROVED;
        }

        $application = $this->latestApplication($user);

        return match ($application?->status) {
            SellerApplication::PENDING => self::PENDING,
            SellerApplication::REJECTED => self::REJECTED,
            default => self::NONE,
        };
    }

    public function latestApplication(User $user): ?SellerApplication
    {
        return SellerApplication::where('user_id', $user->id)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * What the client needs to render the business section correctly.
     *
     * @return array<string, mixed>
     */
    public function payload(User $user): array
    {
        $state = $this->state($user);
        $application = $state === self::APPROVED ? null : $this->latestApplication($user);

        return [
            'state' => $state,
            'can_sell' => $this->canSell($user),
            'can_apply' => in_array($state, [self::NONE, self::REJECTED], true),
            'application' => $application === null ? null : [
                'uuid' => $application->uuid,
                'business_name' => $application->business_name,
                'status' => $application->status,
                'rejection_reason' => $application->rejection_reason,
                'submitted_at' => $application->submitted_at?->toIso8601String(),
                'reviewed_at' => $application->reviewed_at?->toIso8601String(),
            ],
        ];
    }
}

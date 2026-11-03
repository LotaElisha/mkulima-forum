<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentListing;
use App\Models\EquipmentRental;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Every status change on a rental, with who may make it and from where.
 *
 *   owner:  requested -> accepted | declined;  accepted -> completed | cancelled
 *   renter: requested | accepted -> cancelled
 */
class DecideRental
{
    private const RULES = [
        'accept' => ['by' => 'owner', 'from' => ['requested'], 'to' => 'accepted'],
        'decline' => ['by' => 'owner', 'from' => ['requested'], 'to' => 'declined'],
        'complete' => ['by' => 'owner', 'from' => ['accepted'], 'to' => 'completed'],
        'cancel' => ['by' => 'either', 'from' => ['requested', 'accepted'], 'to' => 'cancelled'],
    ];

    public static function actions(): array
    {
        return array_keys(self::RULES);
    }

    public function __invoke(User $actor, EquipmentRental $rental, string $action): EquipmentRental
    {
        $rule = self::RULES[$action];
        $isOwner = $rental->listing->owner_id === $actor->id;
        $isRenter = $rental->renter_id === $actor->id;

        $allowed = match ($rule['by']) {
            'owner' => $isOwner,
            'either' => $isOwner || $isRenter,
        };
        if (! $allowed) {
            throw new AccessDeniedHttpException('Huna ruhusa ya kufanya hivi.');
        }

        return DB::transaction(function () use ($rental, $rule, $action) {
            // Lock the listing so two requests for the same days cannot both be accepted.
            EquipmentListing::whereKey($rental->equipment_listing_id)->lockForUpdate()->first();
            $rental->refresh();

            if (! in_array($rental->status, $rule['from'], true)) {
                throw ValidationException::withMessages(['status' => 'Ombi hili haliwezi kubadilishwa tena.']);
            }
            if ($action === 'accept'
                && CheckAvailability::overlaps($rental->listing, $rental->start_date, $rental->end_date, $rental->id)) {
                throw ValidationException::withMessages(['status' => 'Tarehe hizi tayari zimekubaliwa kwa mkodishaji mwingine.']);
            }

            $rental->update(['status' => $rule['to']]);

            return $rental;
        });
    }
}

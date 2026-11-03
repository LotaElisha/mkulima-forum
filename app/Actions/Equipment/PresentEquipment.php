<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentListing;
use App\Models\EquipmentRental;
use App\Models\User;
use App\Support\Nearby;

class PresentEquipment
{
    /** Public listing. The owner's phone is never in it. */
    public static function listing(EquipmentListing $listing): array
    {
        return [
            'uuid' => $listing->uuid,
            'category' => $listing->category,
            'category_label' => EquipmentListing::CATEGORIES[$listing->category] ?? $listing->category,
            'title' => $listing->title,
            'description' => $listing->description,
            'daily_rate' => (float) $listing->daily_rate,
            'with_operator' => $listing->with_operator,
            'region' => $listing->region,
            'district' => $listing->district,
            'status' => $listing->status,
            'owner' => ['uuid' => $listing->owner?->uuid, 'name' => $listing->owner?->name],
            'distance_km' => Nearby::km($listing->getAttribute('distance_km')),
        ];
    }

    /**
     * A rental as seen by one party. The other party's phone number appears
     * only once the owner has accepted, so a listing cannot be used to
     * harvest numbers.
     */
    public static function rental(EquipmentRental $rental, User $viewer): array
    {
        $isOwner = $rental->listing?->owner_id === $viewer->id;
        $other = $isOwner ? $rental->renter : $rental->listing?->owner;
        $shareContact = $rental->status === 'accepted';

        return [
            'uuid' => $rental->uuid,
            'status' => $rental->status,
            'role' => $isOwner ? 'owner' : 'renter',
            'start_date' => $rental->start_date->toDateString(),
            'end_date' => $rental->end_date->toDateString(),
            'days' => $rental->start_date->diffInDays($rental->end_date) + 1,
            'quoted_total' => (float) $rental->quoted_total,
            'message' => $rental->message,
            'equipment' => $rental->listing ? [
                'uuid' => $rental->listing->uuid,
                'title' => $rental->listing->title,
                'category_label' => EquipmentListing::CATEGORIES[$rental->listing->category] ?? $rental->listing->category,
                'region' => $rental->listing->region,
            ] : null,
            'counterpart' => [
                'name' => $other?->name,
                'phone' => $shareContact ? $other?->phone : null,
            ],
            'created_at' => $rental->created_at?->toIso8601String(),
        ];
    }
}

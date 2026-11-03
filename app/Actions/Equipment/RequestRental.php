<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentListing;
use App\Models\EquipmentRental;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RequestRental
{
    public const MAX_DAYS = 60;

    public function __invoke(User $renter, EquipmentListing $listing, array $input): EquipmentRental
    {
        if ($listing->status !== 'active') {
            throw ValidationException::withMessages(['equipment' => 'Zana hii haipatikani kwa sasa.']);
        }
        if ($listing->owner_id === $renter->id) {
            throw ValidationException::withMessages(['equipment' => 'Huwezi kukodi zana yako mwenyewe.']);
        }

        $start = Carbon::parse($input['start_date'])->startOfDay();
        $end = Carbon::parse($input['end_date'])->startOfDay();
        $days = $start->diffInDays($end) + 1;

        if ($days > self::MAX_DAYS) {
            throw ValidationException::withMessages(['end_date' => 'Muda wa juu wa kukodi ni siku '.self::MAX_DAYS.'.']);
        }
        if (CheckAvailability::overlaps($listing, $start, $end)) {
            throw ValidationException::withMessages(['start_date' => 'Zana hii tayari imekodiwa kwa tarehe hizo.']);
        }

        return EquipmentRental::create([
            'tenant_id' => $renter->tenant_id,
            'equipment_listing_id' => $listing->id,
            'renter_id' => $renter->id,
            'start_date' => $start,
            'end_date' => $end,
            'quoted_total' => $days * (float) $listing->daily_rate,
            'message' => $input['message'] ?? null,
            'status' => 'requested',
        ]);
    }
}

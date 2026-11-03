<?php

namespace App\Actions\Equipment;

use App\Models\EquipmentListing;
use App\Models\EquipmentRental;
use Illuminate\Support\Carbon;

class CheckAvailability
{
    /** True when an accepted rental already holds any day in [start, end]. */
    public static function overlaps(EquipmentListing $listing, Carbon $start, Carbon $end, ?int $exceptRentalId = null): bool
    {
        return EquipmentRental::query()
            ->where('equipment_listing_id', $listing->id)
            ->whereIn('status', EquipmentRental::BLOCKING)
            ->when($exceptRentalId, fn ($q) => $q->where('id', '!=', $exceptRentalId))
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();
    }
}

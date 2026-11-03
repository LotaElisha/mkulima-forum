<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Distance ordering for anything with latitude/longitude columns.
 *
 * Great-circle distance in SQL, so it works on plain PostgreSQL without
 * PostGIS. Rows without coordinates are kept when the caller also filters
 * by region, and sort after every row that has a distance.
 */
final class Nearby
{
    public const DEFAULT_RADIUS_KM = 50;

    public const MAX_RADIUS_KM = 300;

    /** Validation rules shared by every "nearby" endpoint. */
    public static function rules(): array
    {
        return [
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius_km' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_RADIUS_KM],
            'region' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * With lat/lng: rows within the radius, nearest first, plus a
     * distance_km column. Without: filter by region when one is given.
     */
    public static function apply(Builder $query, array $input, string $table): Builder
    {
        $region = $input['region'] ?? null;

        if (! isset($input['lat'], $input['lng'])) {
            return $region ? $query->where("{$table}.region", $region) : $query;
        }

        $lat = (float) $input['lat'];
        $lng = (float) $input['lng'];
        $radius = (int) ($input['radius_km'] ?? self::DEFAULT_RADIUS_KM);

        // least(1, ...) guards acos against rounding just above 1 for identical points.
        $distance = "(6371 * acos(least(1, cos(radians(?)) * cos(radians({$table}.latitude))"
            ." * cos(radians({$table}.longitude) - radians(?))"
            ." + sin(radians(?)) * sin(radians({$table}.latitude)))))";
        $bindings = [$lat, $lng, $lat];

        return $query
            ->select("{$table}.*")
            ->selectRaw("{$distance} as distance_km", $bindings)
            ->where(function (Builder $inner) use ($distance, $bindings, $radius, $region, $table) {
                $inner->where(function (Builder $located) use ($distance, $bindings, $radius, $table) {
                    $located->whereNotNull("{$table}.latitude")
                        ->whereNotNull("{$table}.longitude")
                        ->whereRaw("{$distance} <= ?", [...$bindings, $radius]);
                });
                if ($region) {
                    $inner->orWhere(fn (Builder $q) => $q->whereNull("{$table}.latitude")->where("{$table}.region", $region));
                }
            })
            ->orderByRaw('distance_km asc nulls last');
    }

    public static function km(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 1);
    }
}

<?php

namespace App\Services\Spine;

use App\Models\Crop;
use App\Models\GeoUnit;
use App\Models\ProductCategory;
use App\Models\Topic;
use Illuminate\Support\Facades\Cache;

class TaxonomyService
{
    /**
     * Resolve geography, crop, topic, or product category by ID, UUID, slug, or code.
     */
    public function resolve(string $type, int|string $identifier): mixed
    {
        $cacheKey = "taxonomy:{$type}:" . (is_numeric($identifier) ? "id:{$identifier}" : "str:{$identifier}");

        return Cache::remember($cacheKey, 3600, function () use ($type, $identifier) {
            return match ($type) {
                'geo', 'geography' => is_numeric($identifier)
                    ? GeoUnit::find($identifier)
                    : GeoUnit::where('uuid', $identifier)->orWhere('code', $identifier)->orWhere('name', 'like', $identifier)->first(),

                'crop' => is_numeric($identifier)
                    ? Crop::find($identifier)
                    : Crop::where('uuid', $identifier)->orWhere('slug', $identifier)->orWhere('name', 'like', $identifier)->first(),

                'topic' => is_numeric($identifier)
                    ? Topic::find($identifier)
                    : Topic::where('uuid', $identifier)->orWhere('slug', $identifier)->orWhere('name', 'like', $identifier)->first(),

                'category', 'product_category' => is_numeric($identifier)
                    ? ProductCategory::find($identifier)
                    : ProductCategory::where('uuid', $identifier)->orWhere('slug', $identifier)->orWhere('code', $identifier)->first(),

                default => null,
            };
        });
    }

    public function getRegions()
    {
        return Cache::remember('taxonomy:geo:regions', 3600, function () {
            return GeoUnit::where('type', 'region')->orderBy('name')->get();
        });
    }

    public function getDistricts(?int $regionId = null)
    {
        $key = 'taxonomy:geo:districts:' . ($regionId ?? 'all');
        return Cache::remember($key, 3600, function () use ($regionId) {
            $query = GeoUnit::where('type', 'district');
            if ($regionId) {
                $query->where('parent_id', $regionId);
            }
            return $query->orderBy('name')->get();
        });
    }

    public function getCrops()
    {
        return Cache::remember('taxonomy:crops:all', 3600, function () {
            return Crop::where('is_active', true)->orderBy('name')->get();
        });
    }

    public function getTopics()
    {
        return Cache::remember('taxonomy:topics:all', 3600, function () {
            return Topic::where('is_active', true)->orderBy('name')->get();
        });
    }

    public function getCategories()
    {
        return Cache::remember('taxonomy:categories:all', 3600, function () {
            return ProductCategory::all();
        });
    }
}

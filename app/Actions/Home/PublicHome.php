<?php

namespace App\Actions\Home;

use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\WeatherCache;
use Throwable;

/**
 * The public home reads only what is already stored. It does not call a
 * weather provider during the page render.
 */
class PublicHome
{
    /**
     * @return array{weather: array, prices: list<array>, threads: list<array>}
     */
    public function __invoke(): array
    {
        return [
            'weather' => $this->weather(),
            'prices' => $this->prices(),
            'threads' => $this->threads(),
        ];
    }

    /**
     * @return array{available: bool, location: string, summary: ?string}
     */
    private function weather(): array
    {
        try {
            $row = WeatherCache::query()->orderByDesc('updated_at')->first();
            $current = $row?->current_data;
            if (! is_array($current) || ($current['available'] ?? true) === false) {
                return $this->weatherEmpty();
            }

            $temp = $current['temperature'] ?? null;
            $desc = $current['description'] ?? null;
            $summary = trim(implode(' · ', array_filter([
                $temp !== null ? $temp.'°' : null,
                is_string($desc) ? $desc : null,
            ])));

            return [
                'available' => $summary !== '',
                'location' => (string) ($current['location'] ?? $row->location ?? 'Dar es Salaam'),
                'summary' => $summary !== '' ? $summary : null,
            ];
        } catch (Throwable) {
            return $this->weatherEmpty();
        }
    }

    /**
     * @return array{available: bool, location: string, summary: null}
     */
    private function weatherEmpty(): array
    {
        return [
            'available' => false,
            'location' => 'Dar es Salaam',
            'summary' => null,
        ];
    }

    /**
     * @return list<array{commodity: string, market: string, price: string, date: string}>
     */
    private function prices(): array
    {
        try {
            return MarketPrice::query()
                ->orderByDesc('price_date')
                ->limit(4)
                ->get()
                ->map(fn (MarketPrice $price) => [
                    'commodity' => $price->commodity,
                    'market' => $price->market,
                    'price' => number_format((float) $price->avg_price, 0).' '.$price->currency.'/'.$price->unit,
                    'date' => $price->price_date?->toDateString() ?? '',
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{uuid: string, title: string, replies: int}>
     */
    private function threads(): array
    {
        try {
            return ForumThread::query()
                ->where('status', 'active')
                ->orderByDesc('is_pinned')
                ->latest()
                ->limit(3)
                ->get(['uuid', 'title', 'reply_count'])
                ->map(fn (ForumThread $thread) => [
                    'uuid' => $thread->uuid,
                    'title' => $thread->title,
                    'replies' => (int) $thread->reply_count,
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }
}

<?php

namespace App\Filament\Resources\MarketPriceResource\Pages;

use App\Filament\Resources\MarketPriceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketPrice extends CreateRecord
{
    protected static string $resource = MarketPriceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = auth()->id();
        $data['currency'] ??= 'TZS';
        if (blank($data['avg_price'] ?? null) && isset($data['min_price'], $data['max_price'])) {
            $data['avg_price'] = round(((float) $data['min_price'] + (float) $data['max_price']) / 2, 2);
        }

        return $data;
    }
}

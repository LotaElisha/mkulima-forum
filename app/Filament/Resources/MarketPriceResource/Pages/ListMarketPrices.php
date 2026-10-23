<?php

namespace App\Filament\Resources\MarketPriceResource\Pages;

use App\Filament\Resources\MarketPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketPrices extends ListRecords
{
    protected static string $resource = MarketPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

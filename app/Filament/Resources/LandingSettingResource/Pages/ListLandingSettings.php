<?php

namespace App\Filament\Resources\LandingSettingResource\Pages;

use App\Filament\Resources\LandingSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLandingSettings extends ListRecords
{
    protected static string $resource = LandingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

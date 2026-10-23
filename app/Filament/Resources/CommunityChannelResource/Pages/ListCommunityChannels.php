<?php

namespace App\Filament\Resources\CommunityChannelResource\Pages;

use App\Filament\Resources\CommunityChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommunityChannels extends ListRecords
{
    protected static string $resource = CommunityChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}

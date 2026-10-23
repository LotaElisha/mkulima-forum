<?php

namespace App\Filament\Resources\CommunityChannelResource\Pages;

use App\Filament\Resources\CommunityChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommunityChannel extends EditRecord
{
    protected static string $resource = CommunityChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

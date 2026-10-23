<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityChannelResource\Pages;
use App\Models\CommunityChannel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommunityChannelResource extends Resource
{
    protected static ?string $model = CommunityChannel::class;

    protected static ?string $navigationLabel = 'Community links';

    protected static ?string $modelLabel = 'community link';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(64)->unique(ignoreRecord: true),
            Select::make('platform')->required()->options(array_combine(CommunityChannel::PLATFORMS, CommunityChannel::PLATFORMS)),
            Select::make('channel_type')->required()->options(array_combine(CommunityChannel::CHANNEL_TYPES, CommunityChannel::CHANNEL_TYPES)),
            Textarea::make('description.sw')->label('Maelezo (Kiswahili)')->rows(3),
            TextInput::make('url')->url()->maxLength(2000),
            TextInput::make('phone_number')->tel()->maxLength(32),
            Toggle::make('is_active')->default(true),
            Toggle::make('is_featured')->default(false),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('platform'),
            TextColumn::make('channel_type'),
            IconColumn::make('is_active')->boolean(),
            IconColumn::make('is_featured')->boolean(),
            TextColumn::make('sort_order'),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunityChannels::route('/'),
            'create' => Pages\CreateCommunityChannel::route('/create'),
            'edit' => Pages\EditCommunityChannel::route('/{record}/edit'),
        ];
    }
}

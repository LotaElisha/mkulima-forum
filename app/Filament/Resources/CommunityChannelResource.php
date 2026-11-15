<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityChannelResource\Pages;
use App\Models\CommunityChannel;
use Filament\Actions\CreateAction;
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

    protected static ?string $navigationLabel = 'Viungo vya jamii';

    protected static ?string $modelLabel = 'kiungo cha jamii';

    protected static ?string $pluralModelLabel = 'Viungo vya jamii';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Maudhui';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Jina')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug')->required()->maxLength(64)->unique(ignoreRecord: true),
            Select::make('platform')->label('Jukwaa')->required()->options(array_combine(CommunityChannel::PLATFORMS, CommunityChannel::PLATFORMS)),
            Select::make('channel_type')->label('Aina ya kiungo')->required()->options(array_combine(CommunityChannel::CHANNEL_TYPES, CommunityChannel::CHANNEL_TYPES)),
            Textarea::make('description.sw')->label('Maelezo (Kiswahili)')->rows(3),
            TextInput::make('url')->label('Kiungo (URL)')->url()->maxLength(2000),
            TextInput::make('phone_number')->label('Namba ya simu')->tel()->maxLength(32),
            Toggle::make('is_active')->label('Kinaonekana')->default(true),
            Toggle::make('is_featured')->label('Kimeangaziwa')->default(false),
            TextInput::make('sort_order')->label('Mpangilio')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Jina')->searchable(),
            TextColumn::make('platform')->label('Jukwaa')->badge()->color('gray'),
            TextColumn::make('channel_type')->label('Aina'),
            IconColumn::make('is_active')->label('Kinaonekana')->boolean(),
            IconColumn::make('is_featured')->label('Kimeangaziwa')->boolean(),
            TextColumn::make('sort_order')->label('Mpangilio')->sortable(),
        ])
            ->defaultSort('sort_order')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('Bado hakuna kiungo cha jamii')
            ->emptyStateDescription('Ongeza magrupu ya WhatsApp, chaneli na kurasa za mitandao ya kijamii ambazo wakulima wataziona kwenye programu na tovuti.')
            ->emptyStateActions([CreateAction::make()->label('Ongeza kiungo')]);
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

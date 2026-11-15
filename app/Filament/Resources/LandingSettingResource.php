<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingSettingResource\Pages;
use App\Models\LandingSetting;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LandingSettingResource extends Resource
{
    protected static ?string $model = LandingSetting::class;

    protected static ?string $navigationLabel = 'Ukurasa wa mwanzo';

    protected static ?string $modelLabel = 'maandishi';

    protected static ?string $pluralModelLabel = 'Maandishi ya ukurasa wa mwanzo';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 4;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Maudhui';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Ufunguo')->required()->maxLength(255)->unique(ignoreRecord: true),
            Textarea::make('value')->label('Maandishi')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->label('Ufunguo')->searchable(),
            TextColumn::make('value')->label('Maandishi')->limit(80)->wrap(),
            TextColumn::make('updated_at')->label('Ilibadilishwa')->dateTime('d/m/Y H:i')->sortable(),
        ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('Ukurasa wa mwanzo unatumia maandishi ya kawaida')
            ->emptyStateDescription('Ongeza ufunguo hapa ili kubadilisha kichwa au maelezo kwenye ukurasa wa mwanzo bila kubadilisha msimbo.')
            ->emptyStateActions([CreateAction::make()->label('Ongeza maandishi')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLandingSettings::route('/'),
            'create' => Pages\CreateLandingSetting::route('/create'),
            'edit' => Pages\EditLandingSetting::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingSettingResource\Pages;
use App\Models\LandingSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LandingSettingResource extends Resource
{
    protected static ?string $model = LandingSetting::class;

    protected static ?string $navigationLabel = 'Landing copy';

    protected static ?string $modelLabel = 'landing copy';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
            Textarea::make('value')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->searchable(),
            TextColumn::make('value')->limit(80),
            TextColumn::make('updated_at')->dateTime(),
        ]);
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketPriceResource\Pages;
use App\Models\MarketPrice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketPriceResource extends Resource
{
    protected static ?string $model = MarketPrice::class;

    protected static ?string $navigationLabel = 'Market prices';

    protected static ?string $modelLabel = 'market price';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('commodity')->required()->maxLength(64),
            TextInput::make('market')->required()->maxLength(96),
            TextInput::make('region')->required()->maxLength(64),
            TextInput::make('min_price')->numeric()->required()->minValue(0),
            TextInput::make('max_price')->numeric()->required()->minValue(0),
            TextInput::make('avg_price')->numeric()->minValue(0),
            TextInput::make('unit')->required()->maxLength(32)->default('kg'),
            TextInput::make('currency')->maxLength(8)->default('TZS'),
            DatePicker::make('price_date')->required()->maxDate(now()),
            TextInput::make('source')->maxLength(128),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('commodity')->searchable(),
            TextColumn::make('market')->searchable(),
            TextColumn::make('region'),
            TextColumn::make('avg_price'),
            TextColumn::make('unit'),
            TextColumn::make('price_date')->date(),
            TextColumn::make('source')->toggleable(),
        ])->defaultSort('price_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketPrices::route('/'),
            'create' => Pages\CreateMarketPrice::route('/create'),
            'edit' => Pages\EditMarketPrice::route('/{record}/edit'),
        ];
    }
}

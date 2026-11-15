<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketPriceResource\Pages;
use App\Models\MarketPrice;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketPriceResource extends Resource
{
    protected static ?string $model = MarketPrice::class;

    protected static ?string $navigationLabel = 'Bei za soko';

    protected static ?string $modelLabel = 'bei ya soko';

    protected static ?string $pluralModelLabel = 'Bei za soko';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 5;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Maudhui';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('commodity')->label('Zao')->required()->maxLength(64),
            TextInput::make('market')->label('Soko')->required()->maxLength(96),
            TextInput::make('region')->label('Mkoa')->required()->maxLength(64),
            TextInput::make('min_price')->label('Bei ya chini')->numeric()->required()->minValue(0),
            TextInput::make('max_price')->label('Bei ya juu')->numeric()->required()->minValue(0),
            TextInput::make('avg_price')->label('Bei ya wastani')->numeric()->minValue(0),
            TextInput::make('unit')->label('Kipimo')->required()->maxLength(32)->default('kg'),
            TextInput::make('currency')->label('Sarafu')->maxLength(8)->default('TZS'),
            DatePicker::make('price_date')->label('Tarehe ya bei')->required()->maxDate(now()),
            TextInput::make('source')->label('Chanzo')->maxLength(128),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('commodity')->label('Zao')->searchable(),
            TextColumn::make('market')->label('Soko')->searchable(),
            TextColumn::make('region')->label('Mkoa')->sortable(),
            TextColumn::make('min_price')->label('Chini')->numeric()->toggleable(),
            TextColumn::make('max_price')->label('Juu')->numeric()->toggleable(),
            TextColumn::make('avg_price')->label('Wastani')->numeric(),
            TextColumn::make('unit')->label('Kipimo'),
            TextColumn::make('price_date')->label('Tarehe')->date('d/m/Y')->sortable(),
            TextColumn::make('source')->label('Chanzo')->toggleable(),
        ])
            ->defaultSort('price_date', 'desc')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Bado hakuna bei za soko')
            ->emptyStateDescription('Wakulima wanaona bei hizi kwenye programu. Ongeza bei ya zao na soko lake ili kuanza.')
            ->emptyStateActions([CreateAction::make()->label('Ongeza bei')]);
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentListingResource\Pages;
use App\Models\EquipmentListing;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Listings go live when the owner posts them. Hide anything misleading here;
 * an owner cannot relist a listing an admin has hidden.
 */
class EquipmentListingResource extends Resource
{
    protected static ?string $model = EquipmentListing::class;

    protected static ?string $navigationLabel = 'Vifaa vya kukodi';

    protected static ?string $modelLabel = 'tangazo la kifaa';

    protected static ?string $pluralModelLabel = 'Vifaa vya kukodi';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 2;

    public const STATUSES = ['active' => 'Hai', 'paused' => 'Imesitishwa', 'hidden' => 'Imefichwa'];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Huduma';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('owner:id,name,phone')->withCount('rentals'))
            ->columns([
                TextColumn::make('title')->label('Kichwa')->searchable()->wrap(),
                TextColumn::make('category')->label('Aina')->formatStateUsing(fn (string $state) => EquipmentListing::CATEGORIES[$state] ?? $state),
                TextColumn::make('owner.name')->label('Mmiliki')->searchable(),
                TextColumn::make('owner.phone')->label('Simu')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')->label('Mkoa')->sortable(),
                TextColumn::make('daily_rate')->label('Bei kwa siku')->money('TZS')->sortable(),
                TextColumn::make('rentals_count')->label('Maombi')->sortable(),
                TextColumn::make('status')->label('Hali')->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success', 'hidden' => 'danger', default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Liliwekwa')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                    SelectFilter::make('status')->label('Hali')->options(self::STATUSES),
                    SelectFilter::make('category')->label('Aina')->options(EquipmentListing::CATEGORIES),
            ])
            ->recordActions([
                    Action::make('hide')->label('Ficha')
                        ->icon('heroicon-o-eye-slash')->color('danger')
                        ->visible(fn (EquipmentListing $record) => $record->status !== 'hidden')
                        ->requiresConfirmation()
                        ->modalDescription('Tangazo halitaonekana tena kwa wakulima, na mmiliki hataweza kulirudisha.')
                        ->action(fn (EquipmentListing $record) => $record->update(['status' => 'hidden'])),
                    Action::make('unhide')->label('Rudisha')
                        ->icon('heroicon-o-eye')
                        ->visible(fn (EquipmentListing $record) => $record->status === 'hidden')
                        ->action(fn (EquipmentListing $record) => $record->update(['status' => 'active'])),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateHeading('Bado hakuna kifaa cha kukodi')
            ->emptyStateDescription('Wamiliki huweka trekta, power tiller na vifaa vingine kupitia programu. Matangazo yataonekana hapa, na unaweza kuficha yoyote yenye kupotosha.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipmentListings::route('/'),
        ];
    }
}

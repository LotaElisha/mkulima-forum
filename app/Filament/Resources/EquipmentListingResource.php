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

    protected static ?string $navigationLabel = 'Equipment';

    protected static ?string $modelLabel = 'equipment listing';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('owner:id,name,phone')->withCount('rentals'))
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('category')->formatStateUsing(fn (string $state) => EquipmentListing::CATEGORIES[$state] ?? $state),
                TextColumn::make('owner.name')->label('Owner')->searchable(),
                TextColumn::make('owner.phone')->label('Phone')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')->sortable(),
                TextColumn::make('daily_rate')->money('TZS')->sortable(),
                TextColumn::make('rentals_count')->label('Requests')->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'active' => 'success', 'hidden' => 'danger', default => 'gray',
                }),
                TextColumn::make('created_at')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_combine(EquipmentListing::STATUSES, EquipmentListing::STATUSES)),
                SelectFilter::make('category')->options(EquipmentListing::CATEGORIES),
            ])
            ->recordActions([
                Action::make('hide')
                    ->icon('heroicon-o-eye-slash')->color('danger')
                    ->visible(fn (EquipmentListing $record) => $record->status !== 'hidden')
                    ->requiresConfirmation()
                    ->action(fn (EquipmentListing $record) => $record->update(['status' => 'hidden'])),
                Action::make('unhide')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (EquipmentListing $record) => $record->status === 'hidden')
                    ->action(fn (EquipmentListing $record) => $record->update(['status' => 'active'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipmentListings::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TraceLotResource\Pages;
use App\Models\TraceLot;
use App\Services\Trace\TraceCode;
use App\Services\Trace\TraceRecorder;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Mkulima Trace lots, read-only. Lots flagged for review (a harvest large for
 * its plot, a hand-off whose weights do not match, a disputed hand-off) are
 * marked; a flag never blocks the farmer, it only asks an admin to look.
 */
class TraceLotResource extends Resource
{
    protected static ?string $model = TraceLot::class;

    protected static ?string $navigationLabel = 'Trace lots';

    protected static ?string $modelLabel = 'trace lot';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static string|\UnitEnum|null $navigationGroup = 'Mkulima Trace';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['holder.user:id,name', 'plot:id,region']))
            ->columns([
                TextColumn::make('code')->searchable()->fontFamily('mono')
                    ->url(fn (TraceLot $record) => TraceCode::url($record->code), shouldOpenInNewTab: true),
                TextColumn::make('crop')->searchable(),
                TextColumn::make('product')->placeholder('—'),
                TextColumn::make('quantity_kg')->label('kg')->numeric(1)->sortable(),
                TextColumn::make('holder.user.name')->label('Holder'),
                TextColumn::make('holder.type')->label('Holder type')->badge(),
                TextColumn::make('plot.region')->label('Region'),
                TextColumn::make('status')->badge(),
                TextColumn::make('anomaly_flags')->label('Review')
                    ->getStateUsing(fn (TraceLot $record) => empty($record->anomaly_flags) ? null
                        : collect($record->anomaly_flags)->pluck('flag')->unique()->implode(', ')
                            .($record->anomaly_reviewed_at ? ' (reviewed)' : ''))
                    ->color(fn (TraceLot $record) => $record->hasOpenAnomaly() ? 'danger' : 'gray')
                    ->placeholder('—')->wrap(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filter::make('needs_review')->label('Needs review')
                    ->query(fn ($query) => $query->whereNotNull('anomaly_flags')->whereNull('anomaly_reviewed_at')),
                SelectFilter::make('status')->options(array_combine(
                    ['active', 'in_transfer', 'split', 'merged', 'processed', 'delivered', 'closed'],
                    ['active', 'in_transfer', 'split', 'merged', 'processed', 'delivered', 'closed'],
                )),
            ])
            ->recordActions([
                Action::make('mark_reviewed')
                    ->label('Mark reviewed')
                    ->icon('heroicon-o-check')
                    ->visible(fn (TraceLot $record) => $record->hasOpenAnomaly())
                    ->requiresConfirmation()
                    ->action(fn (TraceLot $record) => app(TraceRecorder::class)->reviewAnomaly(auth()->user(), $record)),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTraceLots::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TraceParticipantResource\Pages;
use App\Models\TraceParticipant;
use App\Services\Trace\TraceRecorder;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Mkulima Trace participants. Farmers can record straight away; collectors,
 * warehouses, processors, transporters and buyers wait here until an admin
 * has checked who they are. Approving is the only thing that marks a
 * participant verified.
 */
class TraceParticipantResource extends Resource
{
    protected static ?string $model = TraceParticipant::class;

    protected static ?string $navigationLabel = 'Trace participants';

    protected static ?string $modelLabel = 'trace participant';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|\UnitEnum|null $navigationGroup = 'Mkulima Trace';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user:id,name,phone,email'))
            ->columns([
                TextColumn::make('user.name')->label('Account')->searchable(),
                TextColumn::make('organisation_name')->label('Organisation')->searchable()->placeholder('—'),
                TextColumn::make('type')->badge(),
                TextColumn::make('code')->copyable()->fontFamily('mono'),
                TextColumn::make('region')->placeholder('—'),
                TextColumn::make('user.phone')->label('Phone')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'active' => 'success', 'pending' => 'warning', default => 'danger',
                }),
                IconColumn::make('verified_at')->label('Verified')->boolean()
                    ->getStateUsing(fn (TraceParticipant $record) => $record->verified_at !== null),
                TextColumn::make('created_at')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(array_combine(TraceParticipant::STATUSES, TraceParticipant::STATUSES)),
                SelectFilter::make('type')->options(array_combine(TraceParticipant::TYPES, TraceParticipant::TYPES)),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (TraceParticipant $record) => $record->status !== 'active' || $record->verified_at === null)
                    ->schema([Textarea::make('note')->label('Note (optional)')->maxLength(500)])
                    ->action(fn (TraceParticipant $record, array $data) => app(TraceRecorder::class)
                        ->decideParticipant(auth()->user(), $record, 'approve', $data['note'] ?? null)),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (TraceParticipant $record) => $record->status === 'pending')
                    ->schema([Textarea::make('note')->label('Reason shown to the applicant')->required()->maxLength(500)])
                    ->action(fn (TraceParticipant $record, array $data) => app(TraceRecorder::class)
                        ->decideParticipant(auth()->user(), $record, 'reject', $data['note'])),
                Action::make('suspend')
                    ->icon('heroicon-o-pause-circle')->color('danger')
                    ->visible(fn (TraceParticipant $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->schema([Textarea::make('note')->label('Reason')->required()->maxLength(500)])
                    ->action(fn (TraceParticipant $record, array $data) => app(TraceRecorder::class)
                        ->decideParticipant(auth()->user(), $record, 'suspend', $data['note'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTraceParticipants::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgronomistResource\Pages;
use App\Models\ServiceProvider;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Agronomists appear in the app only once verified here. Check the licence
 * (TARI / registration) before verifying: farmers act on their advice.
 */
class AgronomistResource extends Resource
{
    protected static ?string $model = ServiceProvider::class;

    protected static ?string $navigationLabel = 'Agronomists';

    protected static ?string $modelLabel = 'agronomist';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('service_type', 'agronomist')->with('user:id,name,phone,email');
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = ServiceProvider::where('service_type', 'agronomist')->where('verification_status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('business_name')->maxLength(255),
            Textarea::make('bio')->maxLength(2000)->columnSpanFull(),
            TextInput::make('region')->required()->maxLength(64),
            TextInput::make('district')->maxLength(64),
            TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90)
                ->helperText('Base for farm visits. Leave empty to list by region only.'),
            TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
            TextInput::make('license_number')->maxLength(100),
            TextInput::make('consultation_fee')->numeric()->minValue(0)->suffix('TZS'),
            TextInput::make('visit_fee')->numeric()->minValue(0)->suffix('TZS'),
            Select::make('verification_status')->options([
                'pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected',
            ])->required(),
            Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->label('Name')->searchable()
                    ->formatStateUsing(fn ($state, ServiceProvider $record) => $state ?: $record->user?->name),
                TextColumn::make('user.phone')->label('Phone')->toggleable(),
                TextColumn::make('region')->searchable()->sortable(),
                TextColumn::make('district')->toggleable(),
                TextColumn::make('license_number')->label('Licence')->toggleable(),
                TextColumn::make('verification_status')->label('Status')->badge()->color(fn (string $state) => match ($state) {
                    'verified' => 'success', 'rejected' => 'danger', default => 'warning',
                }),
                IconColumn::make('latitude')->label('GPS')->boolean()
                    ->getStateUsing(fn (ServiceProvider $record) => $record->latitude !== null),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('verification_status')->options([
                    'pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected',
                ]),
            ])
            ->recordActions([
                Action::make('verify')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (ServiceProvider $record) => $record->verification_status !== 'verified')
                    ->requiresConfirmation()
                    ->modalDescription('The agronomist will be listed to farmers. Confirm the licence first.')
                    ->action(fn (ServiceProvider $record) => $record->update(['verification_status' => 'verified'])),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (ServiceProvider $record) => $record->verification_status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (ServiceProvider $record) => $record->update(['verification_status' => 'rejected'])),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgronomists::route('/'),
            'edit' => Pages\EditAgronomist::route('/{record}/edit'),
        ];
    }
}

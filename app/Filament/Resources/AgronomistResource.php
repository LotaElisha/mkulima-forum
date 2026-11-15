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
    public const STATUS_COLORS = ['verified' => 'success', 'rejected' => 'danger'];

    public const STATUSES = [
        'pending' => 'Inasubiri', 'verified' => 'Amethibitishwa', 'rejected' => 'Amekataliwa',
    ];

    protected static ?string $model = ServiceProvider::class;

    protected static ?string $navigationLabel = 'Wataalamu wa kilimo';

    protected static ?string $modelLabel = 'mtaalamu wa kilimo';

    protected static ?string $pluralModelLabel = 'Wataalamu wa kilimo';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Huduma';

    protected static ?string $recordRouteKeyName = 'uuid';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('service_type', 'agronomist')->with('user:id,name,phone,email');
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Wanasubiri uthibitisho';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = ServiceProvider::where('service_type', 'agronomist')->where('verification_status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('business_name')->label('Jina la biashara')->maxLength(255),
            Textarea::make('bio')->label('Maelezo')->maxLength(2000)->columnSpanFull(),
            TextInput::make('region')->label('Mkoa')->required()->maxLength(64),
            TextInput::make('district')->label('Wilaya')->maxLength(64),
            TextInput::make('latitude')->label('Latitudo')->numeric()->minValue(-90)->maxValue(90)
                ->helperText('Kituo cha kutembelea mashamba. Acha tupu ili aorodheshwe kwa mkoa tu.'),
            TextInput::make('longitude')->label('Longitudo')->numeric()->minValue(-180)->maxValue(180),
            TextInput::make('license_number')->label('Namba ya leseni')->maxLength(100),
            TextInput::make('consultation_fee')->label('Ada ya ushauri')->numeric()->minValue(0)->suffix('TZS'),
            TextInput::make('visit_fee')->label('Ada ya kutembelea shamba')->numeric()->minValue(0)->suffix('TZS'),
            Select::make('verification_status')->label('Hali ya uthibitisho')->options(self::STATUSES)->required(),
            Toggle::make('is_active')->label('Anaonekana kwa wakulima'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->label('Jina')->searchable()
                    ->formatStateUsing(fn ($state, ServiceProvider $record) => $state ?: $record->user?->name),
                TextColumn::make('user.phone')->label('Simu')->toggleable(),
                TextColumn::make('region')->label('Mkoa')->searchable()->sortable(),
                TextColumn::make('district')->label('Wilaya')->toggleable(),
                TextColumn::make('license_number')->label('Leseni')->toggleable(),
                TextColumn::make('verification_status')->label('Hali')->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'warning'),
                IconColumn::make('latitude')->label('GPS')->boolean()
                    ->getStateUsing(fn (ServiceProvider $record) => $record->latitude !== null),
                IconColumn::make('is_active')->label('Hai')->boolean(),
                TextColumn::make('created_at')->label('Alijisajili')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('verification_status')->label('Hali')->options(self::STATUSES),
            ])
            ->recordActions([
                Action::make('verify')->label('Thibitisha')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (ServiceProvider $record) => $record->verification_status !== 'verified')
                    ->requiresConfirmation()
                    ->modalDescription('Mtaalamu ataonekana kwa wakulima. Hakiki leseni yake kwanza.')
                    ->action(fn (ServiceProvider $record) => $record->update(['verification_status' => 'verified'])),
                Action::make('reject')->label('Kataa')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (ServiceProvider $record) => $record->verification_status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (ServiceProvider $record) => $record->update(['verification_status' => 'rejected'])),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Bado hakuna mtaalamu wa kilimo')
            ->emptyStateDescription('Wataalamu hujisajili kupitia programu ya Mkulima. Maombi yao yataonekana hapa ili uhakiki leseni na kuwathibitisha.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgronomists::route('/'),
            'edit' => Pages\EditAgronomist::route('/{record}/edit'),
        ];
    }
}

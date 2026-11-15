<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AgronomistResource;
use App\Filament\Resources\EquipmentListingResource;
use App\Filament\Resources\MarketPriceResource;
use App\Models\EquipmentListing;
use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\Product;
use App\Models\ServiceProvider;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Dashboard summary: what is on the platform and what is waiting on an admin.
 * Counts are live queries, so an empty database shows honest zeros.
 */
class PlatformOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Muhtasari wa jukwaa';

    protected int|array|null $columns = 3;

    protected function getStats(): array
    {
        $pendingAgronomists = ServiceProvider::where('service_type', 'agronomist')
            ->where('verification_status', 'pending')->count();
        $latestPrice = MarketPrice::max('price_date');

        return [
            Stat::make('Watumiaji', number_format(User::count()))
                ->description(number_format(User::where('created_at', '>=', now()->subDays(7))->count()).' wapya wiki hii')
                ->icon('heroicon-o-users'),
            Stat::make('Bidhaa sokoni', number_format(Product::where('status', 'active')->count()))
                ->description('Bidhaa zinazoonekana kwa wanunuzi')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Mada za jukwaa', number_format(ForumThread::where('status', 'active')->count()))
                ->description('Mijadala iliyo wazi')
                ->icon('heroicon-o-chat-bubble-left-right'),
            Stat::make('Wataalamu wanaosubiri', number_format($pendingAgronomists))
                ->description($pendingAgronomists > 0 ? 'Kagua leseni kisha thibitisha' : 'Hakuna anayesubiri')
                ->descriptionColor($pendingAgronomists > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-academic-cap')
                ->url(AgronomistResource::getUrl()),
            Stat::make('Vifaa vya kukodi', number_format(EquipmentListing::where('status', 'active')->count()))
                ->description('Matangazo yanayoonekana')
                ->icon('heroicon-o-truck')
                ->url(EquipmentListingResource::getUrl()),
            Stat::make('Bei za soko', number_format(MarketPrice::count()))
                ->description($latestPrice
                    ? 'Ya karibuni: '.Carbon::parse($latestPrice)->format('d/m/Y')
                    : 'Bado hakuna bei. Ongeza ya kwanza')
                ->descriptionColor($latestPrice ? 'gray' : 'warning')
                ->icon('heroicon-o-banknotes')
                ->url(MarketPriceResource::getUrl()),
        ];
    }
}

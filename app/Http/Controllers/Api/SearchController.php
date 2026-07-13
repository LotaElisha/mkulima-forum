<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\MarketPrice;
use App\Models\Product;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Global search across products, forum threads, verified experts,
 * service providers and market prices — with Swahili↔English
 * agricultural synonym expansion so "maize" finds "mahindi" and
 * vice versa.
 */
class SearchController extends Controller
{
    /**
     * Bidirectional agricultural synonym groups (Swahili + English +
     * common variants). Any term in a group expands the search to the
     * whole group.
     */
    protected const SYNONYMS = [
        ['mahindi', 'maize', 'corn'],
        ['mpunga', 'mchele', 'rice', 'paddy'],
        ['maharage', 'maharagwe', 'beans'],
        ['ndizi', 'banana', 'plantain'],
        ['viazi', 'potato', 'potatoes'],
        ['nyanya', 'tomato', 'tomatoes'],
        ['vitunguu', 'onion', 'onions'],
        ['alizeti', 'sunflower'],
        ['kahawa', 'coffee'],
        ['chai', 'tea'],
        ['pamba', 'cotton'],
        ['korosho', 'cashew'],
        ['muhogo', 'mihogo', 'cassava'],
        ['miwa', 'sugarcane', 'sugar cane'],
        ['mbolea', 'fertilizer', 'fertiliser', 'manure'],
        ['mbegu', 'seed', 'seeds'],
        ['dawa', 'viatilifu', 'pesticide', 'pesticides', 'herbicide', 'fungicide'],
        ['kuku', 'chicken', 'poultry'],
        ["ng'ombe", 'ngombe', 'cattle', 'cow', 'cows', 'dairy'],
        ['mbuzi', 'goat', 'goats'],
        ['samaki', 'fish', 'fisheries'],
        ['nyuki', 'asali', 'bee', 'bees', 'honey', 'beekeeping'],
        ['udongo', 'soil'],
        ['umwagiliaji', 'irrigation'],
        ['wadudu', 'pests', 'pest', 'insects'],
        ['magonjwa', 'ugonjwa', 'disease', 'diseases'],
        ['mavuno', 'harvest', 'yield'],
        ['trekta', 'trekita', 'tractor'],
    ];

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $terms = $this->expandTerms($validated['q']);

        $like = function ($query, array $columns) use ($terms) {
            $query->where(function ($q) use ($columns, $terms) {
                foreach ($columns as $column) {
                    foreach ($terms as $term) {
                        $q->orWhere($column, 'like', "%{$term}%");
                    }
                }
            });
        };

        $products = Product::where('status', 'active')
            ->tap(fn ($q) => $like($q, ['name', 'description']))
            ->orderByDesc('is_verified')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'uuid' => $p->uuid,
                'name' => $p->name,
                'price' => (float) $p->price,
                'currency' => $p->currency,
                'unit' => $p->unit,
                'is_verified' => (bool) $p->is_verified,
            ]);

        $threads = ForumThread::where('status', 'active')
            ->tap(fn ($q) => $like($q, ['title', 'body']))
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'uuid' => $t->uuid,
                'title' => $t->title,
                'snippet' => str($t->body)->limit(100)->toString(),
                'region' => $t->region,
            ]);

        $experts = User::where('is_verified_expert', true)
            ->where('status', 'active')
            ->tap(fn ($q) => $like($q, ['name', 'expert_title']))
            ->limit(3)
            ->get()
            ->map(fn ($u) => [
                'uuid' => $u->uuid,
                'name' => $u->name,
                'expert_title' => $u->expert_title,
            ]);

        $providers = ServiceProvider::where('is_active', true)
            ->where('verification_status', 'verified')
            ->tap(fn ($q) => $like($q, ['business_name', 'bio', 'region']))
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->map(fn ($sp) => [
                'uuid' => $sp->uuid,
                'business_name' => $sp->business_name,
                'service_type' => $sp->service_type,
                'region' => $sp->region,
                'rating' => (float) $sp->rating,
            ]);

        $prices = MarketPrice::query()
            ->tap(fn ($q) => $like($q, ['commodity', 'market', 'region']))
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('max(id)')->from('market_prices')->groupBy('commodity', 'market');
            })
            ->orderByDesc('price_date')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'uuid' => $p->uuid,
                'commodity' => $p->commodity,
                'market' => $p->market,
                'avg_price' => (float) $p->avg_price,
                'unit' => $p->unit,
                'price_date' => $p->price_date->toDateString(),
            ]);

        $total = $products->count() + $threads->count() + $experts->count()
            + $providers->count() + $prices->count();

        return response()->json([
            'query' => $validated['q'],
            'expanded_terms' => $terms,
            'total' => $total,
            'results' => [
                'products' => $products,
                'threads' => $threads,
                'experts' => $experts,
                'providers' => $providers,
                'market_prices' => $prices,
            ],
            // Shown by clients when nothing matches.
            'suggestions' => $total === 0
                ? ['mahindi', 'mbolea', 'mbegu', 'kuku', 'nyanya', 'bei za soko']
                : [],
        ]);
    }

    /**
     * Expand the raw query into unique search terms via the synonym map.
     */
    protected function expandTerms(string $query): array
    {
        $words = preg_split('/\s+/', mb_strtolower(trim($query)));
        $terms = [mb_strtolower(trim($query))];

        foreach ($words as $word) {
            if (mb_strlen($word) < 2) {
                continue;
            }
            $terms[] = $word;
            foreach (self::SYNONYMS as $group) {
                if (in_array($word, $group, true)) {
                    $terms = array_merge($terms, $group);
                }
            }
        }

        return array_values(array_unique($terms));
    }
}

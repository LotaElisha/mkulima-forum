<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1600'],
            'type' => ['nullable', 'string', 'in:alert,otp,marketing,advisory'],
        ]);

        $user = $request->user();

        // Log the SMS
        $sms = SmsLog::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user?->id,
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'type' => $request->input('type', 'alert'),
            'gateway' => 'africastalking',
            'status' => 'sent',
        ]);

        // In production, integrate with Africa's Talking or Twilio
        // For demo, just log it

        return response()->json([
            'message' => 'SMS queued successfully',
            'sms_id' => $sms->uuid,
        ]);
    }

    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $logs = SmsLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'messages' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        // Webhook for SMS delivery callbacks
        return response()->json([
            'status' => 'received',
        ]);
    }

    public function receive(Request $request): JsonResponse
    {
        // Webhook for receiving SMS
        $request->validate([
            'from' => ['required', 'string'],
            'text' => ['required', 'string'],
            'messageId' => ['nullable', 'string'],
        ]);

        // Process incoming SMS
        $from = $request->input('from');
        $text = strtolower(trim($request->input('text')));

        $response = match (true) {
            str_contains($text, 'bei') => $this->handlePriceQuery($from, $text),
            str_contains($text, 'hali') => $this->handleWeatherQuery($from, $text),
            str_contains($text, 'msaada') => $this->handleHelpQuery($from),
            default => 'Karibu MkulimaForum! Tumia: BEI [bidhaa], HALI [mkoa], au MSAADA',
        };

        return response()->json([
            'message' => $response,
        ]);
    }

    private function handlePriceQuery(string $phone, string $text): string
    {
        // "BEI <commodity>" — answer from real recorded market prices only.
        $parts = preg_split('/\s+/', trim($text));
        $commodity = $parts[1] ?? null;

        if (!$commodity) {
            return "Tumia: BEI [zao], mfano: BEI mahindi";
        }

        $prices = \App\Models\MarketPrice::where('commodity', 'like', "%{$commodity}%")
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('max(id)')->from('market_prices')->groupBy('market');
            })
            ->orderByDesc('price_date')
            ->limit(3)
            ->get();

        if ($prices->isEmpty()) {
            return "Samahani, hatuna bei za '{$commodity}' kwa sasa. "
                . "Angalia app: https://mkulima.hudumapro.com";
        }

        $lines = $prices->map(fn ($p) => sprintf(
            '%s: TZS %s/%s (%s)',
            $p->market,
            number_format((float) $p->avg_price),
            $p->unit,
            $p->price_date->format('d/m')
        ))->implode("\n");

        return "Bei za {$prices->first()->commodity}:\n{$lines}\n"
            . "App: https://mkulima.hudumapro.com";
    }

    private function handleWeatherQuery(string $phone, string $text): string
    {
        // "HALI [mkoa]" — answer from the real weather service; never fabricate.
        $parts = preg_split('/\s+/', trim($text));
        $location = $parts[1] ?? 'Dar es Salaam';

        $weather = app(\App\Services\WeatherService::class)->getCurrentWeather($location);

        if (($weather['available'] ?? true) === false) {
            return "Samahani, taarifa za hali ya hewa za {$location} hazipatikani kwa sasa.";
        }

        $stale = ($weather['is_stale'] ?? false) ? ' (taarifa za awali)' : '';

        return "Hali ya hewa {$weather['location']}{$stale}:\n"
            . "Joto: {$weather['temperature']}°C\n"
            . "Unyevu: {$weather['humidity']}%\n"
            . "Upepo: {$weather['wind_speed']} m/s\n"
            . ucfirst($weather['description'] ?? '');
    }

    private function handleHelpQuery(string $phone): string
    {
        return "MkulimaForum Msaada:\n"
            . "1. BEI [bidhaa] - Angalia bei\n"
            . "2. HALI - Hali ya hewa\n"
            . "3. MSAADA - Msaada huu\n"
            . "4. PIGA 0714524007\n"
            . "App: https://mkulima.hudumapro.com";
    }
}

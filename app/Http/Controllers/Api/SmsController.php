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
        // Extract product name
        $parts = explode(' ', $text);
        $product = $parts[1] ?? 'mahindi';

        return "Bei za $product leo:\n"
            . "Dar: TZS 45,000/sack\n"
            . "Arusha: TZS 42,000/sack\n"
            . "Mwanza: TZS 44,000/sack\n"
            . "Pakua app: https://mkulima.hudumapro.com";
    }

    private function handleWeatherQuery(string $phone, string $text): string
    {
        return "Hali ya hewa leo:\n"
            . "Joto: 28-32°C\n"
            . "Unyevu: 75%\n"
            . "Upepo: 15 km/h\n"
            . "Mvua: 20%\n"
            . "Ushauri: Wakati mzuri wa kupanda";
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

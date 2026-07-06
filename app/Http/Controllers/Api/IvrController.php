<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IvrLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IvrController extends Controller
{
    public function handleIncoming(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => ['required', 'string'],
            'phoneNumber' => ['required', 'string'],
            'text' => ['nullable', 'string'],
            'isActive' => ['required', 'boolean'],
        ]);

        $sessionId = $request->input('sessionId');
        $phone = $request->input('phoneNumber');
        $input = $request->input('text', '');

        // Log the call
        IvrLog::create([
            'session_id' => $sessionId,
            'phone' => $phone,
            'input' => $input,
            'action' => 'incoming',
        ]);

        // Build IVR response
        $response = $this->buildIvrResponse($input ?? '');

        return response()->json($response);
    }

    public function handleCallback(Request $request): JsonResponse
    {
        $request->validate([
            'sessionId' => ['required', 'string'],
            'phoneNumber' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        IvrLog::create([
            'session_id' => $request->input('sessionId'),
            'phone' => $request->input('phoneNumber'),
            'action' => 'callback',
            'status' => $request->input('status'),
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function buildIvrResponse(string $input): array
    {
        $menu = match ($input) {
            '' => [
                'say' => 'Karibu Mkulima Forum. Chagua huduma. Bonyeza moja kwa bei za soko. Bonyeza mbili kwa hali ya hewa. Bonyeza tatu kuongea na mtaalamu. Bonyeza sifuri kuacha.',
                'play' => null,
                'getDigits' => [
                    'numDigits' => 1,
                    'timeout' => 10,
                    'finishOnKey' => '#',
                ],
            ],
            '1' => [
                'say' => 'Bei za leo. Mahindi elfu arobaini na tano kwa kila gunia. Mpunga elfu hamsini kwa kila gunia. Mbogamboga elfu kumi kwa kila kiro. Rudi nyuma bonyeza kumi.',
                'play' => null,
                'getDigits' => [
                    'numDigits' => 2,
                    'timeout' => 10,
                ],
            ],
            '2' => [
                'say' => 'Hali ya hewa leo. Joto la digrii ishirini na nane hadi thelathini na mbili. Unyevu asilimia sabini na tano. Nafasi ya mvua asilimia ishirini. Wakati mzuri wa kupanda.',
                'play' => null,
                'getDigits' => [
                    'numDigits' => 2,
                    'timeout' => 10,
                ],
            ],
            '3' => [
                'say' => 'Unaungana na mtaalamu wa kilimo. Tafadhali subiri.',
                'play' => null,
                'dial' => [
                    'phoneNumbers' => '+255714524007',
                    'record' => true,
                ],
            ],
            '10', '0' => [
                'say' => 'Asante kwa kutumia Mkulima Forum. Kwaheri.',
                'play' => null,
            ],
            default => [
                'say' => 'Chaguo sio sahihi. Tafadhali jaribu tena.',
                'play' => null,
                'getDigits' => [
                    'numDigits' => 1,
                    'timeout' => 10,
                ],
            ],
        };

        return [
            'sessionId' => $input,
            'action' => $menu,
        ];
    }
}

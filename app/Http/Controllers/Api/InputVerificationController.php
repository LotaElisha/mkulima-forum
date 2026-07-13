<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CounterfeitAlert;
use App\Models\RegisteredInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * "Kagua Dawa" — counterfeit agricultural-input detection.
 *
 * Design principle: the system NEVER claims certainty it doesn't have.
 * It reports evidence (registry match / mismatch, confirmed local alerts,
 * label extraction) with honest guidance, and always points high-risk
 * cases to TPRI / a registered agrovet.
 */
class InputVerificationController extends Controller
{
    /* ------------------------------------------------------------------
     | Public: registry lookup
     * ---------------------------------------------------------------- */

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $registryCount = RegisteredInput::count();
        $query = trim($validated['q']);

        $matches = RegisteredInput::where(function ($w) use ($query) {
                $w->where('name', 'like', "%{$query}%")
                    ->orWhere('registration_number', 'like', "%{$query}%")
                    ->orWhere('manufacturer', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($i) => $this->presentInput($i));

        // Any confirmed community alerts mentioning this product?
        $alerts = CounterfeitAlert::where('status', 'confirmed')
            ->where(function ($w) use ($query) {
                $w->where('product_name', 'like', "%{$query}%")
                    ->orWhere('registration_number', 'like', "%{$query}%");
            })
            ->latest('reviewed_at')
            ->limit(5)
            ->get()
            ->map(fn ($a) => $this->presentAlert($a));

        return response()->json([
            'query' => $query,
            'registry_count' => $registryCount,
            'registry_ready' => $registryCount > 0,
            'matches' => $matches,
            'related_alerts' => $alerts,
            'guidance' => $this->guidanceFor($matches, $alerts, $registryCount),
        ]);
    }

    /* ------------------------------------------------------------------
     | Public (auth): label photo check — Gemini extracts label details,
     | we cross-reference the registry. AI extracts; the registry decides.
     * ---------------------------------------------------------------- */

    public function checkLabel(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'message' => 'Huduma ya kusoma lebo haipatikani kwa sasa. Tumia utafutaji wa orodha badala yake.',
                'available' => false,
            ], 503);
        }

        $extraction = $this->extractLabel($request->file('image')->getRealPath());

        if (!$extraction) {
            return response()->json([
                'message' => 'Imeshindikana kusoma lebo. Hakikisha picha ni ya karibu na yenye mwanga wa kutosha, kisha jaribu tena.',
                'available' => true,
                'readable' => false,
            ], 200);
        }

        // Cross-reference the registry by registration number first (strong
        // signal), then by product name.
        $match = null;
        if (!empty($extraction['registration_number'])) {
            $match = RegisteredInput::where('registration_number', $extraction['registration_number'])->first();
        }
        if (!$match && !empty($extraction['product_name'])) {
            $match = RegisteredInput::where('name', 'like', '%' . $extraction['product_name'] . '%')->first();
        }

        $registryCount = RegisteredInput::count();

        $verdict = match (true) {
            $match && $match->status === 'banned' => 'banned',
            $match && $match->status === 'withdrawn' => 'withdrawn',
            (bool) $match => 'found_registered',
            $registryCount === 0 => 'registry_empty',
            default => 'not_found',
        };

        return response()->json([
            'available' => true,
            'readable' => true,
            'extracted' => [
                'product_name' => $extraction['product_name'] ?? null,
                'registration_number' => $extraction['registration_number'] ?? null,
                'manufacturer' => $extraction['manufacturer'] ?? null,
                'label_warnings' => $extraction['label_warnings'] ?? [],
            ],
            'verdict' => $verdict,
            'registry_match' => $match ? $this->presentInput($match) : null,
            'guidance' => $this->labelGuidance($verdict, $match),
        ]);
    }

    /* ------------------------------------------------------------------
     | Public: confirmed alerts per region + verification checklist
     * ---------------------------------------------------------------- */

    public function alerts(Request $request): JsonResponse
    {
        $request->validate(['region' => ['nullable', 'string', 'max:64']]);

        $alerts = CounterfeitAlert::where('status', 'confirmed')
            ->when($request->input('region'), fn ($q, $r) => $q->where('region', 'like', "%{$r}%"))
            ->latest('reviewed_at')
            ->paginate(20);

        $alerts->getCollection()->transform(fn ($a) => $this->presentAlert($a));

        return response()->json($alerts);
    }

    /**
     * Hand-verification checklist — served from the API so agronomists can
     * update it without an app release. Transparent rules, not a black box.
     */
    public function checklist(): JsonResponse
    {
        return response()->json([
            'title' => 'Kagua Dawa Kabla ya Kununua',
            'items' => [
                ['key' => 'registration', 'text' => 'Lebo ina namba ya usajili ya TPRI (dawa) au TFRA (mbolea)?', 'weight' => 'high'],
                ['key' => 'seal', 'text' => 'Kifungashio kimefungwa kiwandani — hakuna dalili ya kufunguliwa au kujazwa upya?', 'weight' => 'high'],
                ['key' => 'expiry', 'text' => 'Tarehe ya mwisho wa matumizi (expiry) ipo na haijapita?', 'weight' => 'high'],
                ['key' => 'label_quality', 'text' => 'Maandishi ya lebo ni safi — hakuna makosa ya maandishi wala rangi zilizofifia?', 'weight' => 'medium'],
                ['key' => 'batch', 'text' => 'Namba ya batch/lot ipo kwenye kifungashio?', 'weight' => 'medium'],
                ['key' => 'price', 'text' => 'Bei inalingana na soko? Bei ya chini kupita kiasi ni dalili ya hatari.', 'weight' => 'medium'],
                ['key' => 'dealer', 'text' => 'Unanunua kwa agrovet aliyesajiliwa mwenye leseni inayoonekana?', 'weight' => 'high'],
                ['key' => 'receipt', 'text' => 'Muuzaji anatoa risiti yenye jina la duka?', 'weight' => 'low'],
            ],
            'advice' => 'Ukikosa majibu ya "Ndiyo" kwenye vipengele vya uzito wa juu (high), '
                . 'usinunue — ripoti kwenye app na uwasiliane na afisa ugani au TPRI.',
            'version' => 1,
        ]);
    }

    /* ------------------------------------------------------------------
     | Auth: community counterfeit report
     * ---------------------------------------------------------------- */

    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'product_type' => ['nullable', 'string', 'in:' . implode(',', RegisteredInput::TYPES)],
            'registration_number' => ['nullable', 'string', 'max:64'],
            'batch_number' => ['nullable', 'string', 'max:64'],
            'dealer_name' => ['nullable', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:64'],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:10240'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('counterfeit-reports', 'public');
        }

        $alert = CounterfeitAlert::create([
            ...collect($validated)->except('photo')->all(),
            'reporter_id' => $request->user()->id,
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Ripoti imepokelewa. Itahakikiwa na wataalamu kabla ya kutangazwa. Asante kwa kulinda wakulima wenzako.',
            'alert' => ['uuid' => $alert->uuid, 'status' => $alert->status],
        ], 201);
    }

    /* ------------------------------------------------------------------
     | Admin: registry CRUD + alert review queue
     * ---------------------------------------------------------------- */

    public function registryIndex(Request $request): JsonResponse
    {
        $inputs = RegisteredInput::query()
            ->when($request->input('q'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('registration_number', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(30);

        $inputs->getCollection()->transform(fn ($i) => $this->presentInput($i));

        return response()->json($inputs);
    }

    public function registryStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:' . implode(',', RegisteredInput::TYPES)],
            'registration_number' => ['nullable', 'string', 'max:64'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'distributor' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:' . implode(',', RegisteredInput::STATUSES)],
            'source' => ['required', 'string', 'max:128'],
            'source_date' => ['nullable', 'date'],
        ]);

        $input = RegisteredInput::create($validated);

        return response()->json([
            'message' => 'Bidhaa imeongezwa kwenye orodha.',
            'input' => $this->presentInput($input),
        ], 201);
    }

    public function registryUpdate(Request $request, string $uuid): JsonResponse
    {
        $input = RegisteredInput::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:' . implode(',', RegisteredInput::TYPES)],
            'registration_number' => ['sometimes', 'nullable', 'string', 'max:64'],
            'manufacturer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'distributor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', RegisteredInput::STATUSES)],
            'source' => ['sometimes', 'string', 'max:128'],
            'source_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $input->update($validated);

        return response()->json([
            'message' => 'Imesasishwa.',
            'input' => $this->presentInput($input->fresh()),
        ]);
    }

    public function registryDestroy(string $uuid): JsonResponse
    {
        RegisteredInput::where('uuid', $uuid)->firstOrFail()->delete();

        return response()->json(['message' => 'Imefutwa.']);
    }

    public function alertQueue(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', 'string', 'in:pending,confirmed,dismissed']]);

        $alerts = CounterfeitAlert::with('reporter:id,uuid,name,role')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        $alerts->getCollection()->transform(fn ($a) => [
            ...$this->presentAlert($a),
            'status' => $a->status,
            'reporter' => $a->reporter?->only(['uuid', 'name', 'role']),
            'admin_notes' => $a->admin_notes,
        ]);

        return response()->json($alerts);
    }

    public function reviewAlert(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:confirmed,dismissed'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $alert = CounterfeitAlert::where('uuid', $uuid)->firstOrFail();

        if ($alert->status !== 'pending') {
            return response()->json(['message' => 'Ripoti hii tayari imeshughulikiwa.'], 422);
        }

        $alert->update([
            'status' => $validated['decision'],
            'reviewed_by' => $request->user()->id,
            'admin_notes' => $validated['notes'] ?? null,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => $validated['decision'] === 'confirmed'
                ? 'Tahadhari imethibitishwa na sasa inaonekana kwa wakulima wa mkoa huo.'
                : 'Ripoti imekataliwa.',
        ]);
    }

    /* ------------------------------------------------------------------
     | Internals
     * ---------------------------------------------------------------- */

    protected function extractLabel(string $imagePath): ?array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

            $prompt = 'This is a photo of an agricultural input label (pesticide, herbicide, '
                . 'fungicide, fertilizer or veterinary product) sold in Tanzania. Extract ONLY '
                . 'what is actually visible on the label — never guess or invent values. '
                . 'Return JSON with keys: product_name (string|null), registration_number '
                . '(string|null — TPRI or TFRA registration number if printed), manufacturer '
                . '(string|null), label_warnings (array of strings — any visible quality '
                . 'problems: blurry printing, spelling errors, missing expiry date, '
                . 'tampered seal). If a field is not readable, use null.';

            $model = config('services.gemini.model', 'gemini-2.5-flash');
            $response = Http::timeout(25)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . config('services.gemini.api_key'),
                [
                    'contents' => [[
                        'parts' => [
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $imageData]],
                        ],
                    ]],
                    'generationConfig' => ['response_mime_type' => 'application/json'],
                ]
            );

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                $result = $text ? json_decode($text, true) : null;

                if (is_array($result)) {
                    return $result;
                }
            }
        } catch (\Exception $e) {
            Log::error('Label extraction failed: ' . $e->getMessage());
        }

        return null;
    }

    protected function presentInput(RegisteredInput $input): array
    {
        return [
            'uuid' => $input->uuid,
            'name' => $input->name,
            'type' => $input->type,
            'registration_number' => $input->registration_number,
            'manufacturer' => $input->manufacturer,
            'status' => $input->status,
            'source' => $input->source,
            'source_date' => $input->source_date?->toDateString(),
        ];
    }

    protected function presentAlert(CounterfeitAlert $alert): array
    {
        return [
            'uuid' => $alert->uuid,
            'product_name' => $alert->product_name,
            'product_type' => $alert->product_type,
            'registration_number' => $alert->registration_number,
            'batch_number' => $alert->batch_number,
            'dealer_name' => $alert->dealer_name,
            'region' => $alert->region,
            'district' => $alert->district,
            'description' => $alert->description,
            'confirmed_at' => $alert->reviewed_at?->toIso8601String(),
        ];
    }

    protected function guidanceFor($matches, $alerts, int $registryCount): string
    {
        if ($alerts->isNotEmpty()) {
            return 'TAHADHARI: Kuna ripoti zilizothibitishwa za bidhaa feki zinazofanana na hii. '
                . 'Kagua kwa makini sana kabla ya kununua.';
        }
        if ($matches->contains(fn ($m) => $m['status'] === 'banned')) {
            return 'ONYO: Bidhaa hii IMEPIGWA MARUFUKU. Usinunue wala kutumia — ripoti muuzaji.';
        }
        if ($matches->isNotEmpty()) {
            return 'Bidhaa imepatikana kwenye orodha ya usajili. Kumbuka: bidhaa feki huiga majina '
                . 'halali — hakikisha pia kifungashio, muhuri na tarehe ya matumizi (tumia orodha ya ukaguzi).';
        }
        if ($registryCount === 0) {
            return 'Orodha ya usajili bado inajazwa na wataalamu wetu. Hakiki moja kwa moja na TPRI '
                . '(tpri.go.tz) au afisa ugani wa eneo lako.';
        }

        return 'Bidhaa HAIKUPATIKANA kwenye orodha ya usajili — hii ni dalili ya hatari. '
            . 'Usinunue kabla ya kuhakiki na TPRI au afisa ugani. Ukiiona ikiuzwa, ripoti hapa.';
    }

    protected function labelGuidance(string $verdict, ?RegisteredInput $match): string
    {
        return match ($verdict) {
            'banned' => 'ONYO KALI: Bidhaa hii IMEPIGWA MARUFUKU (' . ($match?->source ?? '') . '). Usinunue wala kutumia. Ripoti muuzaji kwa mamlaka.',
            'withdrawn' => 'TAHADHARI: Usajili wa bidhaa hii UMEONDOLEWA. Usinunue kabla ya kuhakiki na TPRI.',
            'found_registered' => 'Lebo inalingana na bidhaa iliyosajiliwa. Kumbuka: bidhaa feki huiga lebo halali — '
                . 'hakikisha pia muhuri wa kifungashio, tarehe ya matumizi na bei (tumia orodha ya ukaguzi).',
            'registry_empty' => 'Tumesoma lebo, lakini orodha yetu ya usajili bado inajazwa. Hakiki namba ya usajili '
                . 'moja kwa moja na TPRI (tpri.go.tz) au afisa ugani.',
            default => 'DALILI YA HATARI: Maelezo ya lebo HAYAKUPATIKANA kwenye orodha ya usajili. '
                . 'Usinunue kabla ya kuhakiki na TPRI au afisa ugani — na ripoti bidhaa hii hapa ili kulinda wengine.',
        };
    }
}

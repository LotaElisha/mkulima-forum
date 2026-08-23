<?php

namespace App\Console\Commands;

use App\Services\AI\AIService;
use Illuminate\Console\Command;

/**
 * Answers "why is the plant scanner failing?" in one command, on the server
 * where the credentials actually are.
 *
 * The scanner's failure message is deliberately vague to the farmer
 * ("Uchambuzi wa picha haukufanikiwa kwa sasa") and the underlying reason only
 * reaches the Laravel log. That is right for the user and useless for whoever
 * has to fix it, so this prints the distinction that matters: a rejected key,
 * an exhausted quota, and a blocked network all look identical from the app
 * and need completely different responses.
 */
class AiCheck extends Command
{
    protected $signature = 'mkulima:ai-check
        {--image= : Path to a JPEG to run a real vision call against}
        {--models : List every model this API key can actually see}
        {--model= : Test a specific model id instead of the configured one}';

    protected $description = 'Check that the configured AI provider can actually be reached and used';

    public function handle(AIService $ai): int
    {
        $this->info('AI configuration');
        $this->line('');

        $key = (string) config('services.gemini.api_key', '');
        $model = (string) config('services.gemini.model', 'gemini-2.0-flash');

        $this->line('  provider (env fallback) : gemini');
        $this->line('  model                   : '.($model ?: '<not set>'));
        $this->line('  api key                 : '.($key === ''
            ? '<EMPTY - this alone will fail every scan>'
            : 'set, '.strlen($key).' chars, ending '.substr($key, -4)));

        $dbProviders = \App\Models\AiProvider::withoutGlobalScopes()->count();
        $this->line('  providers in database   : '.$dbProviders.($dbProviders === 0
            ? ' (falling back to .env)'
            : ''));
        $this->line('');

        if ($key === '' && $dbProviders === 0) {
            $this->error('No API key and no database provider. Set GEMINI_API_KEY or add a provider in Admin -> AI.');

            return self::FAILURE;
        }

        if ($this->option('models')) {
            return $this->listModels($key);
        }

        $this->info('Live call');
        $this->line('');

        $image = $this->option('image');

        try {
            if ($image) {
                if (! is_file($image)) {
                    $this->error("No such file: {$image}");

                    return self::FAILURE;
                }
                $response = $ai->analyzeImage(
                    'plant_diagnosis',
                    $image,
                    'Reply with JSON: {"ok": true}',
                    ['require_json' => true],
                );
            } else {
                $response = $ai->generateText(
                    'plant_diagnosis',
                    [['role' => 'user', 'content' => 'Reply with the single word: OK']],
                    $this->option('model') ? ['model' => $this->option('model')] : [],
                );
            }

            $this->line('  latency  : '.$response->latencyMs.' ms');
            $this->line('  model    : '.($response->model ?: 'unknown'));
            $this->line('  response : '.str($response->content ?? '')->limit(120));
            $this->line('');
            $this->info('AI provider reachable and answering.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $this->line('');
            $this->error('Call failed: '.$message);
            $this->line('');
            $this->warn($this->interpret($message));

            return self::FAILURE;
        }
    }


    /**
     * Ask the key what it can see.
     *
     * Settles two questions that otherwise take a support thread each: whether
     * a configured model id still exists (Google retires them, and a retired
     * id fails with 404 or 400 rather than anything that says "retired"), and
     * whether the Gemma models are reachable on the same key as Gemini. They
     * are - same host, same endpoint, same key, different model id - but that
     * is much easier to believe when the key prints them.
     */
    private function listModels(string $key): int
    {
        $url = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com'), '/')
            .'/v1beta/models?key='.$key.'&pageSize=200';

        // The application registers a throwing HTTP client, so a non-2xx here
        // arrives as an exception rather than a response. Catch it: an
        // unreachable provider is the case this command exists to diagnose,
        // and printing a Laravel stack trace instead of an explanation would
        // be the same failure the app itself was making.
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(20)
                ->withOptions(['http_errors' => false])
                ->get($url);
        } catch (\Throwable $e) {
            $this->error('Could not list models: '.$e->getMessage());
            $this->line('');
            $this->warn($this->interpret($e->getMessage()));

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error('Could not list models: HTTP '.$response->status());
            $this->line('');
            $this->warn($this->interpret((string) $response->status()));

            return self::FAILURE;
        }

        $models = collect($response->json('models') ?? [])
            ->map(fn ($m) => [
                'id' => str_replace('models/', '', $m['name'] ?? ''),
                'methods' => implode(', ', $m['supportedGenerationMethods'] ?? []),
            ])
            ->filter(fn ($m) => $m['id'] !== '')
            ->sortBy('id');

        $gemma = $models->filter(fn ($m) => str_starts_with($m['id'], 'gemma'));
        $gemini = $models->filter(fn ($m) => str_starts_with($m['id'], 'gemini'));

        $configured = (string) config('services.gemini.model');
        $hasConfigured = $models->contains(fn ($m) => $m['id'] === $configured);

        $this->info('Gemini models ('.$gemini->count().')');
        foreach ($gemini as $m) {
            $this->line('  '.$m['id'].($m['id'] === $configured ? '   <-- configured' : ''));
        }

        $this->line('');
        $this->info('Gemma models ('.$gemma->count().')');
        if ($gemma->isEmpty()) {
            $this->line('  none visible to this key');
        }
        foreach ($gemma as $m) {
            $this->line('  '.$m['id']);
        }

        $this->line('');
        if (! $hasConfigured) {
            $this->error("GEMINI_MODEL is set to '{$configured}', which this key cannot see.");
            $this->warn('Every call will fail until this is a model id from the list above.');

            return self::FAILURE;
        }

        $this->info("GEMINI_MODEL '{$configured}' is available.");

        return self::SUCCESS;
    }

    private function interpret(string $message): string
    {
        return match (true) {
            str_contains($message, '400') => 'HTTP 400 - the request was malformed, or the model name is wrong for this API version. Check GEMINI_MODEL.',
            str_contains($message, '401') => 'HTTP 401 - the API key was not accepted. Regenerate it in Google AI Studio.',
            str_contains($message, '403') => 'HTTP 403 - the key is rejected, the Generative Language API is not enabled on that Google Cloud project, or the key has an HTTP-referrer/IP restriction that does not include this server. Check all three, in that order.',
            str_contains($message, '429') => 'HTTP 429 - quota exhausted. Check usage limits on the project.',
            str_contains($message, '404') => 'HTTP 404 - the model does not exist. gemini-2.0-flash and gemini-1.5-flash are current; older names were retired.',
            str_contains($message, 'cURL') || str_contains($message, 'timed out') || str_contains($message, 'Could not resolve') => 'Network failure - this server could not reach generativelanguage.googleapis.com at all. Check egress rules and DNS.',
            default => 'Unrecognised failure. The full message above is what the provider returned.',
        };
    }
}

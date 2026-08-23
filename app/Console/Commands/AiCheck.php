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
    protected $signature = 'mkulima:ai-check {--image= : Path to a JPEG to run a real vision call against}';

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

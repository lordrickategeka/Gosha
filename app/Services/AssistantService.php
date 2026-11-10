<?php

namespace App\Services;

use App\Domains\Platform\Services\IntegrationHttpClient;
use App\Models\User;
use App\Support\AssistantKnowledge;
use Illuminate\Support\Facades\Log;

class AssistantService
{
    public function __construct(private AssistantKnowledge $knowledge) {}

    public static function available(): bool
    {
        $url = config('assistant.base_url');

        return (bool) config('assistant.enabled') && is_string(config('assistant.api_key')) && trim(config('assistant.api_key')) !== ''
            && is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
            && parse_url($url, PHP_URL_SCHEME) === 'https' && ! parse_url($url, PHP_URL_USER) && ! parse_url($url, PHP_URL_PASS);
    }

    public function ask(User $user, string $question): array
    {
        if (! self::available()) {
            return $this->unavailable();
        }
        try {
            $docs = $this->knowledge->documents($user);
            $response = IntegrationHttpClient::make()->baseUrl(rtrim(config('assistant.base_url'), '/'))
                ->withToken(config('assistant.api_key'))->acceptJson()->asJson()->connectTimeout(5)->timeout(55)
                ->post('/v1/chat', [
                    'system' => 'garagehq',
                    'actor' => 'user:'.hash_hmac('sha256', (string) $user->id, config('app.key')),
                    'role' => $this->knowledge->role($user), 'question' => $question, 'docs' => $docs,
                ]);
            if ($response->status() === 429) {
                return ['status' => 429, 'body' => ['message' => 'The assistant is busy. Please wait a minute and try again.']];
            }
            $answer = $response->json('answer');
            if (! $response->successful() || ! is_string($answer) || trim($answer) === '') {
                Log::warning('GarageHQ assistant returned an invalid response.', ['status' => $response->status()]);

                return $this->unavailable();
            }

            // Source links must match the role-filtered documentation we supplied.
            $allowed = array_column($docs['supportGuides'], 'title', 'primaryPath');
            $sources = [];
            foreach (is_array($response->json('sources')) ? $response->json('sources') : [] as $source) {
                $path = is_array($source) ? ($source['path'] ?? null) : null;
                if (is_string($path) && isset($allowed[$path])) {
                    $sources[$path] = ['title' => $allowed[$path], 'path' => $path];
                }
            }
            // The legacy shared API uses a Fotex-specific fallback for unmatched questions.
            if (str_contains($answer, "I couldn't find that in the current Fotex Ops guides.")) {
                $answer = "I couldn't find that in the GarageHQ user guide. Try asking about a specific workflow, or contact your business administrator.";
            }

            return ['status' => 200, 'body' => ['answer' => mb_substr(trim($answer), 0, 6000), 'sources' => array_slice(array_values($sources), 0, 8)]];
        } catch (\Throwable) {
            // Never log prompts, authorization headers, upstream bodies or personal data.
            Log::warning('GarageHQ assistant could not be reached.');

            return $this->unavailable();
        }
    }

    private function unavailable(): array
    {
        return ['status' => 503, 'body' => ['message' => 'The assistant is unavailable right now. Please use the user guide or try again later.']];
    }
}

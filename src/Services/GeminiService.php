<?php

namespace Bhzoon\ProfanityChecker\Services;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Throwable;

class GeminiService
{
    public const SETTING_API_KEY = 'bhzoon.profanity_checker.api_key';

    private const MODEL    = 'gemini-2.0-flash';
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

	private SettingsRepositoryInterface $settings;
	private Client $http;

    public function __construct(SettingsRepositoryInterface $settings, ?Client $http = null)
    {
        $this->settings = $settings;
        $this->http     = $http ?? new Client([
            'timeout'         => 7.0,
            'connect_timeout' => 3.0,
        ]);
    }

    public function apiKey(): string
    {
        return trim((string) $this->settings->get(self::SETTING_API_KEY));
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    /**
     * @return array{blocked:bool,reason:string,labels?:array}|array{blocked:false,reason:string}
     */
    public function moderateText(string $text): array
    {
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return ['blocked' => false, 'reason' => 'NotConfigured'];
        }

        try {
            $payload = $this->buildPayload($text);

            $resp = $this->http->post(sprintf(self::ENDPOINT, self::MODEL), [
                'query'   => ['key' => $apiKey],
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => $payload,
            ]);

            $data = json_decode((string) $resp->getBody(), true);
			$verdict = $this->extractVerdict($data);
            return $verdict;
        } catch (GuzzleException $e) {
            // network / HTTP layer issues
            return ['blocked' => false, 'reason' => 'GeminiHttpError'];
        } catch (Throwable $e) {
            // parsing / unexpected structure
            return ['blocked' => false, 'reason' => 'GeminiParseError'];
        }
    }

    private function buildPayload(string $text): array
    {
        $instruction = <<<TXT
You are a content safety classifier for a public forum. Classify ONLY the user text.
Return ONLY JSON (no markdown, no prose) with exactly:
{
  "blocked": boolean,
  "reason": "Profanity" | "HateSpeech" | "Harassment" | "SexualContent" | "Violence" | "Other" | "None",
  "labels": string[]
}
TXT;

        return [
            'contents' => [[
                'role'  => 'user',
                'parts' => [[ 'text' => $instruction . "\n\nText:\n" . $text ]],
            ]],
            'generationConfig' => [
                'temperature'        => 0,
                'candidateCount'     => 1,
                'response_mime_type' => 'application/json', // <- ask for raw JSON
            ],
        ];
    }

    /**
     * Accepts both raw JSON in the 'text' part or markdown-fenced JSON.
     */
    private function extractVerdict(array $data): array
    {
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (is_string($text) && preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', trim($text), $m)) {
            $text = $m[1];
        }
        $decoded = is_string($text) ? json_decode($text, true) : null;

        if (is_array($decoded) && array_key_exists('blocked', $decoded)) {
            return [
                'blocked' => (bool) ($decoded['blocked'] ?? false),
                'reason'  => (string) ($decoded['reason']  ?? ''),
                'labels'  => array_values(array_filter((array) ($decoded['labels'] ?? []), 'is_string')),
            ];
        }

        return ['blocked' => false, 'reason' => 'UnstructuredResponse'];
    }
}

<?php

namespace Bhzoon\ProfanityChecker\Services;

use Flarum\Settings\SettingsRepositoryInterface;
use GuzzleHttp\Client;
use Throwable;

class GeminiService
{
	public const SETTING_API_KEY = 'bhzoon.profanity_checker.api_key';
	private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

	private SettingsRepositoryInterface $settings;
	private Client $http;

	public function __construct(SettingsRepositoryInterface $settings, ?Client $http = null)
	{
		$this->settings = $settings;
		$this->http = $http ?? new Client(['timeout' => 7.0, 'connect_timeout' => 3.0]);
	}

	public function apiKey(): string
	{
		return trim((string) $this->settings->get(self::SETTING_API_KEY));
	}

	public function isConfigured(): bool
	{
		return $this->apiKey() !== '';
	}

	public function moderateText(string $text): array
	{
		$apiKey = $this->apiKey();
		if ($apiKey === '') {
			return ['blocked' => false, 'reason' => ''];
		}

		try {
			$payload = $this->buildPayload($text);

			$resp = $this->http->post(self::ENDPOINT, [
				'query'   => ['key' => $apiKey],
				'headers' => ['Content-Type' => 'application/json'],
				'json'    => $payload,
			]);

			$data = json_decode((string) $resp->getBody(), true);
			return $this->extractVerdict($data);
		} catch (Throwable $e) {
			return ['blocked' => false, 'reason' => ''];
		}
	}

	private function buildPayload(string $text): array
	{
		$prompt = <<<PROMPT
Classify the following forum post for policy violations.
Return STRICT JSON with keys:
- blocked (boolean)
- reason (short string)
- labels (array of strings)

Text:
\"\"\"{$text}\"\"\"
PROMPT;

		return [
			'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]],
		];
	}

	private function extractVerdict(array $data): array
	{
		$modelText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
		$decoded = json_decode($modelText, true);

		if (is_array($decoded) && array_key_exists('blocked', $decoded)) {
			return [
				'blocked' => (bool)($decoded['blocked'] ?? false),
				'reason'  => (string)($decoded['reason'] ?? ''),
				'details' => $decoded,
			];
		}

		return ['blocked' => false, 'reason' => ''];
	}
}

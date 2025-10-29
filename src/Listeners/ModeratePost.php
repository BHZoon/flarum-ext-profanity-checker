<?php

namespace Bhzoon\ProfanityChecker\Listeners;

use Bhzoon\ProfanityChecker\Services\GeminiService;
use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\Foundation\ValidationException;

class ModeratePost
{
	private GeminiService $gemini;

	public function __construct(GeminiService $gemini)
	{
		$this->gemini = $gemini;
	}

	public function handle(Saving $event): void
	{
		if (!$event->post instanceof CommentPost) return;

		$newContent = $event->data['attributes']['content'] ?? null;
		if ($newContent === null) return;

		if ($event->actor && $event->actor->hasPermission('bhzoon.profanity.bypass')) {
			return;
		}


		if (!$this->gemini->isConfigured()) return;

		$verdict = $this->gemini->moderateText($newContent);

		if ($verdict['blocked'] ?? false) {
			$reason = (string)($verdict['reason'] ?? 'Content policy');
			throw new ValidationException([
				'content' => "Your post was blocked: {$reason}."
			]);
		}
	}
}

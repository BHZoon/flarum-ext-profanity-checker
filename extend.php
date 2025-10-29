<?php

use Flarum\Extend;
use Bhzoon\ProfanityChecker\Listeners\ModeratePost;

return [
	(new Extend\Frontend('forum'))->js(__DIR__.'/js/dist/forum.js'),
	(new Extend\Frontend('admin'))->js(__DIR__.'/js/dist/admin.js'),

	(new Extend\Settings())->serializeToForum(
		'bhzoon.profanity_checker.api_key',
		'bhzoon.profanity_checker.api_key'
	),

	(new Extend\Event)->listen(
		\Flarum\Post\Event\Saving::class,
		ModeratePost::class
	),

	new Extend\Locales(__DIR__.'/locale'),
];

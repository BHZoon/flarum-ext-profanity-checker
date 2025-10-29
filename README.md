# Profanity Checker for Flarum (Gemini)

A Flarum extension that uses **Google Gemini** to automatically moderate new posts for profanity, hate speech, and other policy violations. Blocked posts are rejected with a clear validation message, and privileged users can be allowed to bypass checks via permission.

---

## ✨ Features

* ✅ Server-side moderation of **new posts** using Gemini (via HTTP)
* 🔑 Admin setting to store **Gemini API key**
* 🛡️ Permission: **Bypass profanity checks** (e.g. for trusted moderators)
* 🧩 Minimal UI: extension settings page with a single API key field
* 🧪 Friendly to local dev (Workbench), supports Docker setups

---

## 📦 Requirements

* Flarum **1.8+**
* PHP **8.1+**
* A **Gemini API key** (Google AI Studio)
* PHP extensions required by Flarum + **guzzlehttp/guzzle** (installed as a dependency)

---

## 🛠️ Installation

### Option A: From a VCS repository (GitHub)

If this package is **not on Packagist**, add a VCS repository to your Flarum root `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:BHZoon/flarum-ext-profanity-checker.git"
    }
  ]
}
```

Then require the package (adjust the vendor/name if you changed it):

```bash
composer require bhzoon/flarum-ext-profanity-checker:* --prefer-dist
php flarum cache:clear
```

### Option B: Local development via Workbench

If you’re developing locally inside Flarum’s `workbench/`:

1. Add a path repository in the **Flarum root** `composer.json`:

   ```json
   {
     "repositories": {
       "workbench": {
         "type": "path",
         "url": "workbench/*",
         "options": { "symlink": true }
       }
     }
   }
   ```
2. Require your extension by its package name:

   ```bash
   composer require bhzoon/flarum-ext-profanity-checker:* --prefer-source
   php flarum cache:clear
   ```

> After changing PHP namespaces/files, run `composer dump-autoload -o`.

---

## ⚙️ Configuration

1. In **Admin → Extensions → Profanity Checker**, paste your **Gemini API key**.
2. Optional: set **Permissions → “Bypass profanity checks”** for roles that should be exempt.

**Extension ID:** `bhzoon-profanity-checker`
**Setting key:** `bhzoon.profanity_checker.api_key`
**Permission key:** `bhzoon.profanity.bypass`

---

## 🔍 How it works

* Listens to `Flarum\Post\Event\Saving` when a new post is created.
* Sends the post’s plain text to Gemini (`gemini-2.0-flash:generateContent`) with a strict JSON classification prompt.
* Parses the model’s JSON response (expected keys: `blocked`, `reason`, `labels`).
* If `blocked = true` **and** the actor doesn’t have the **bypass** permission, it throws a `Flarum\Foundation\ValidationException` so the UI shows a clear, inline error.

---

## 🔐 Privacy

Only the text content of the **new post** is sent to Gemini for classification. No user passwords or secrets are transmitted. You control when/if the feature runs by configuring the API key and assigning permissions.

---

## 🧰 Developer Notes

### PHP namespace & IDs

* Namespace: `Bhzoon\ProfanityChecker`
* Extension ID: `bhzoon-profanity-checker`

### Frontend (Admin)

* Admin settings page is built in TypeScript and compiled with Flarum’s webpack config.
* During development:

  ```bash
  cd workbench/flarum-ext-profanity-checker/js
  npm install
  npm run dev     # or: npm run build
  ```
* Clear cache after changing frontend locales or JS entrypoints:

  ```bash
  php flarum cache:clear
  ```

### Typical file layout

```
flarum-ext-profanity-checker/
├── composer.json
├── extend.php
├── resources/
│   └── locale/
│       └── en.yml
├── src/
│   ├── Listeners/
│   │   └── ModeratePost.php
│   └── Services/
│       └── GeminiService.php
└── js/
    ├── admin.ts               # Entrypoint
    └── src/admin/
        ├── index.ts
        └── components/ProfanityCheckerSettingsPage.tsx
```

---

## 🧯 Troubleshooting

* **“ValidationException Facade” error**
  Use `Flarum\Foundation\ValidationException` (not Laravel’s `Illuminate\Validation\ValidationException`).

* **Settings not saving / 500 on /api/settings**
  Ensure write permissions for `storage/`, `public/assets/`, and `bootstrap/cache/` in the container:

  ```bash
  chown -R www-data:www-data storage public/assets bootstrap/cache
  chmod -R 775 storage public/assets bootstrap/cache
  ```

* **Permission not visible**
  Rebuild admin JS and clear the cache:

  ```bash
  npm run build
  php flarum cache:clear
  ```

* **Listener not found**
  Run `composer dump-autoload -o` after renaming/moving PHP classes.

---

## 🧪 Roadmap

* Configurable model & safety threshold
* Per-tag / per-category policies
* Admin log with last N moderation decisions
* Optional auto-flag instead of hard block

---

## 🤝 Contributing

Issues and PRs are welcome. Please include:

* Steps to reproduce
* Expected vs. actual behavior
* Flarum + PHP versions and extension version
* Logs/screenshots if relevant

---

## 📄 License

MIT © 2025 BHZoon

---

## 🙌 Acknowledgements

Built with ❤️ for Flarum. Powered by **Google Gemini** and **Guzzle**.

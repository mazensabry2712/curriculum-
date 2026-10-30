# Development setup

## Requirements

- PHP 8.3 or later. PHP 8.4 is recommended.
- Composer 2.
- MySQL 8+.
- Node.js 22 and npm.
- Laravel Herd is supported for Windows development.
- A provider API key is required only for live AI extraction and AI-generated recommendations.

## Windows / Herd

~~~powershell
Set-Location C:\Herd
git clone https://github.com/mazensabry2712/curriculum-.git
Set-Location C:\Herd\curriculum-
php -v
composer --version
node --version
npm --version
~~~

Check that the CLI PHP version is the Herd PHP version, not another global PHP installation.

Create a MySQL database in phpMyAdmin:

~~~sql
CREATE DATABASE csga_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
~~~

Install dependencies and create the environment file:

~~~powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
~~~

Configure database credentials in .env. Publish the Laravel AI SDK configuration:

~~~powershell
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
~~~

Configure AI provider credentials in the local .env only:

~~~dotenv
OPENAI_API_KEY=your-private-key
CSGA_AI_PROVIDER=openai
CSGA_AI_MODEL=gpt-4.1-mini
CSGA_AI_TIMEOUT=120
~~~

Run migrations, seed roles and the initial skills catalog, then build assets:

~~~powershell
php artisan optimize:clear
php artisan migrate --seed
npm install
npm run build
php artisan serve
~~~

In a second terminal:

~~~powershell
Set-Location C:\Herd\curriculum-
php artisan queue:work --tries=2 --timeout=180
~~~

Visit http://127.0.0.1:8000 and create an organization workspace.

## Configuration reference

| Variable | Purpose |
|---|---|
| APP_TIMEZONE | Defaults to Africa/Cairo |
| DB_* | MySQL connection |
| QUEUE_CONNECTION | Defaults to database |
| OPENAI_API_KEY | Private OpenAI provider key |
| CSGA_AI_PROVIDER | Provider name supported by Laravel AI SDK |
| CSGA_AI_MODEL | Model identifier |
| CSGA_AI_TIMEOUT | Provider timeout in seconds |
| CSGA_MAX_DOCUMENT_KB | PDF upload limit, default 15 MB |

## Tests and builds

~~~powershell
php artisan test
npm run build
~~~

Run only the scoring tests:

~~~powershell
php artisan test tests/Unit/GapScoreCalculatorTest.php
~~~

## Queue operations

The queue worker must run for syllabus extraction, job-description extraction, and AI recommendation jobs.

~~~powershell
php artisan queue:work --tries=2 --timeout=180
php artisan queue:failed
php artisan queue:retry all
~~~

For production, configure a supervised queue worker and consider Redis if database queue throughput becomes insufficient.

## Security checklist

- Never commit .env, API keys, real student records, or uploaded private syllabi.
- Uploaded syllabus files are stored on Laravel's private local disk.
- Keep APP_DEBUG=false in production.
- Use HTTPS and secure cookies in production.
- Restrict document upload and review actions to authorized academic roles.
- Use synthetic student data and a test AI key for demonstrations.
- Back up the database before applying production migrations.
- Confirm provider data processing and retention terms with the institution before sending confidential syllabi.

## Troubleshooting

**Composer lock mismatch:** refresh dependencies with Composer, inspect composer.lock, and commit it alongside composer.json.

**AI jobs remain queued:** check QUEUE_CONNECTION and run queue:work.

**AI jobs fail:** inspect storage/logs/laravel.log and verify provider key, provider name, model access, rate limits, and private file access.

**Vite manifest missing:** run npm install and npm run build.

**No students appear in analysis:** create student accounts in the same organization and verify assessment records.

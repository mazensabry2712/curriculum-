# Curriculum Skills Gap Analyzer (CSGA)

AI-assisted, evidence-led analysis of academic curricula and individual student skills against labor-market requirements.

## What it does

- Manage organizations, departments, programs, versioned curricula, courses, and learning outcomes.
- Maintain a shared skill taxonomy with categories and aliases.
- Define occupations, job profiles, and weighted skill requirements.
- Upload course syllabus PDFs and extract outcomes and skills asynchronously with AI.
- Review AI suggestions before they become approved curriculum mappings.
- Analyze curriculum coverage against a target job profile.
- Maintain student portfolios, assessments, and career targets.
- Analyze student readiness using the latest verified assessment per skill.
- Generate transparent rule-based recommendations and optional AI drafts.
- Preserve analysis snapshots so results can be audited later.

## Technology

| Layer | Choice |
|---|---|
| Backend | PHP 8.3+ / Laravel 13 |
| Database | MySQL 8+ |
| UI | Blade + Tailwind CSS 4 |
| Interactive components | Livewire 4 available |
| AI | Laravel AI SDK with configurable provider |
| Authorization | Spatie Laravel Permission |
| Background work | Laravel Queue |
| Tests | Pest 5 |
| Assets | Vite 8 / Node.js 22 |

## Quick start (Windows / Laravel Herd)

Requirements: PHP 8.3+, Composer 2, MySQL 8+, Node.js 22, npm.

~~~powershell
git clone https://github.com/mazensabry2712/curriculum-.git
Set-Location .\curriculum-
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
~~~

Create a MySQL database named csga_db, then set DB_HOST, DB_DATABASE, DB_USERNAME, and DB_PASSWORD in .env.

Publish the AI SDK configuration and provider resources:

~~~powershell
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
~~~

Set the private provider key and model in .env:

~~~dotenv
OPENAI_API_KEY=
CSGA_AI_PROVIDER=openai
CSGA_AI_MODEL=gpt-4.1-mini
CSGA_AI_TIMEOUT=120
~~~

Run the database setup and build:

~~~powershell
php artisan migrate --seed
npm run build
php artisan serve
~~~

In a second terminal, start a worker for AI and recommendation jobs:

~~~powershell
php artisan queue:work --tries=2 --timeout=180
~~~

Open http://127.0.0.1:8000 and create an organization workspace. Detailed instructions are in docs/SETUP.md.

## Scoring methodology

For required skill i, with required level R, current level C, and importance weight W:

\[
Gap_i = \max(0, (R_i-C_i)/R_i)\times100
\]

\[
Alignment = \frac{\sum W_i \times \min(C_i,R_i)/R_i}{\sum W_i}\times100
\]

Curriculum coverage uses approved course-skill mappings and credit-hour weights. Student analysis uses verified assessments only. An unassessed student skill is not treated as a zero. Assessment coverage is reported separately from alignment.

See docs/ACADEMIC_METHODOLOGY.md for definitions, assumptions, and evaluation.

## AI governance

AI extracts structured suggestions with evidence and confidence. Instructors or advisors must review them before they become official mappings or job requirements. Files are private by default. Uploading a syllabus requires explicit consent to submit its content to the configured AI provider.

See docs/AI_PIPELINE.md.

## Team workflow

Use main as the stable integration branch. Contributors should work on feature branches and submit pull requests. Migrations and shared data contracts should be coordinated by one integration owner.

See docs/TEAM_WORKFLOW.md.

## Documentation

- docs/SETUP.md
- docs/ARCHITECTURE.md
- docs/DATA_MODEL.md
- docs/AI_PIPELINE.md
- docs/ACADEMIC_METHODOLOGY.md
- docs/TEAM_WORKFLOW.md

## Status

The repository contains the Laravel application foundation, academic and skills domains, labor-market and student modules, AI agents and queued jobs, deterministic analysis services, primary web screens, migrations, seeders, tests, and GitHub Actions validation. A real provider API key and end-to-end testing with approved sample data are still required before a graduation demonstration.

No real student records, private syllabus files, or API secrets should be committed.

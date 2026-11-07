# 02 — Package and Dependency Plan

> Compatibility snapshot reviewed on **2026-11-07**. Versions are recommendations, not installed dependencies. Do not install all candidates at once. Resolve versions with Composer, inspect the dependency tree and security advisories, run tests, then commit the lock-file change in a reviewed PR.

## Principles

- Prefer Laravel core when it already solves the problem.
- Every dependency must have one named use case, an owner, tests, and an exit path.
- Avoid duplicate packages that solve the same problem.
- Do not add a package just because it is common in Laravel projects.
- Keep runtime dependencies separate from development-only tooling.
- Run `composer audit` before and after every dependency change.

## Existing packages — keep and use intentionally

| Package | Current constraint | Current responsibility | Decision |
|---|---:|---|---|
| `laravel/framework` | `^13.17` | Application framework, auth, validation, policies, queues, scheduler, storage, mail, cache | Keep |
| `laravel/ai` | `^0.11.2` | AI agents and structured output | Keep; do not add another AI SDK |
| `livewire/livewire` | `^4.0` | Optional server-driven interactive UI | Keep for targeted use; current UI is Blade |
| `spatie/laravel-permission` | `^8.3` | Roles and permissions | Keep; complete Policies and permission enforcement |
| `pestphp/pest` | `^5.3` | Automated tests | Keep |
| `pestphp/pest-plugin-laravel` | `^5.0` | Laravel test helpers | Keep |
| `laravel/pint` | `^1.27` | PHP formatting | Keep |
| `laravel/boost` | `^2.2` | Local development assistance | Keep as development-only tooling |
| `laravel/tinker` | `^3.0` | Local application inspection | Keep |
| `laravel/pail` | `^1.2.5` | Local log tailing | Keep as development tooling |

## Recommended additions

### 1. Audit trail — high priority

| Package | Recommended constraint | Use |
|---|---:|---|
| `spatie/laravel-activitylog` | `^5.1` | Record who changed academic data, reviewed AI suggestions, verified assessments, changed roles, or exported sensitive data. |

**Why:** the application has explicit human approval stages and multi-organization data. A traceable actor/action/time record materially improves trust and auditability.

**Implementation notes:**
- Log meaningful business events, not every noisy model attribute.
- Never store passwords, API keys, raw private PDFs, or unnecessary student personal data in log properties.
- Include organization context in searchable event metadata and enforce tenant visibility on the audit screen.
- Test that one organization cannot read another organization's activity.
- Keep the application's own analysis snapshots; an activity log is not a replacement for them.

Compatibility checked: Activitylog 5.1.4 requires PHP 8.4+ and supports Illuminate/Laravel 13.  
Source: [Packagist — spatie/laravel-activitylog](https://packagist.org/packages/spatie/laravel-activitylog) · [Changelog](https://github.com/spatie/laravel-activitylog/blob/main/CHANGELOG.md)

### 2. Excel/CSV import and export — high priority

| Package | Recommended constraint | Use |
|---|---:|---|
| `maatwebsite/excel` | `^4.0` | Import and export courses, students, skills, occupation/job profile requirements, and analysis tables. |

**Why:** academic and labor-market data is naturally tabular; bulk import/export will be useful for university administrators and project demonstrations.

**Implementation notes:**
- Prefer the maintained 4.x line; do not add the old 3.1 line to a new Laravel 13 implementation.
- Validate file extension, MIME, size, headers, row count, and each row.
- Preview before import; use row-level validation errors and a transaction or staged import.
- Scope every import to the authenticated organization; never trust an organization ID from the workbook.
- Queue large imports and export jobs; show progress and safe failure messages.
- Protect against formula injection when exporting untrusted text to CSV/XLSX.
- Check required PHP extensions such as ZIP and XML on developer and production environments.

Compatibility checked: Laravel Excel 4.0.6 supports PHP 8.3+ and Laravel 12/13.  
Sources: [Packagist](https://packagist.org/packages/maatwebsite/excel) · [Installation requirements](https://docs.laravel-excel.com/4.x/getting-started/installation.html) · [Supported versions](https://docs.laravel-excel.com/4.x/getting-started/support.html)

### 3. PDF reports — high priority

| Package | Recommended constraint | Use |
|---|---:|---|
| `barryvdh/laravel-dompdf` | `^3.1` | Render print-ready PDF reports from dedicated Blade templates. |

**Why:** analysis reports need a downloadable, consistent format for graduation demonstrations and institutional review.

**Implementation notes:**
- Use a dedicated report layout, not a screenshot of the dashboard.
- Keep remote resource loading disabled unless explicitly required and safely allow-listed.
- Escape all user-supplied content and test malicious HTML/URLs.
- Use deterministic fonts and page-break rules.
- If Arabic/RTL becomes a requirement, validate fonts, shaping, and page layout before promising full support.
- Do not install both DomPDF and another PDF-rendering package for the same simple reports.

Compatibility checked: Laravel DomPDF 3.1.2 supports Illuminate/Laravel 13 and PHP 8.1+.  
Sources: [Packagist](https://packagist.org/packages/barryvdh/laravel-dompdf) · [Release notes](https://github.com/barryvdh/laravel-dompdf/releases)

### 4. Static analysis — high priority, development only

| Package | Recommended constraint | Use |
|---|---:|---|
| `larastan/larastan` | `^3.14` | Find type, relationship, query, and Laravel-specific problems before runtime. |

**Implementation notes:**
- Start with a baseline configuration and analyze `app/`.
- Add gradually to CI; avoid introducing hundreds of unrelated legacy warnings in one PR.
- Increase strictness as modules are improved.
- Keep PHPStan/Larastan out of production runtime dependencies.

Compatibility checked: Larastan 3.14.0 supports PHP 8.2–8.5 and Laravel 13.  
Sources: [Larastan GitHub](https://github.com/larastan/larastan) · [Release information](https://github.com/larastan/larastan/releases)

### 5. Error monitoring — recommended before deployment

| Package | Recommended constraint | Use |
|---|---:|---|
| `sentry/sentry-laravel` | `^4.29` | Capture production exceptions and optionally performance traces. |

**Implementation notes:**
- Enable only in environments where the project has a Sentry DSN.
- Scrub credentials, student identifiers, uploaded document content, and sensitive request fields.
- Configure environment/release tags.
- Do not send personal data by default.
- Keep local logs usable when Sentry is disabled.

Compatibility checked: Sentry Laravel 4.29.0 supports Illuminate/Laravel 13.  
Sources: [Packagist](https://packagist.org/packages/sentry/sentry-laravel) · [Laravel compatibility](https://github.com/getsentry/sentry-laravel)

### 6. Database and private-file backups — deployment phase

| Package | Recommended constraint | Use |
|---|---:|---|
| `spatie/laravel-backup` | `^10.4` | Scheduled database and selected private-file backups, with retention and backup health checks. |

**Important compatibility condition:** version 10.4.0 requires PHP 8.4+ and compatible Laravel 12.40+/13 components, plus ZIP support. The host must also provide a working database dump utility for the selected database driver. Confirm the actual hosting environment before installation.

**Implementation notes:**
- Back up the database and private evidence files to a separate protected destination.
- Encrypt backups at rest where supported.
- Exclude cache, temporary uploads, logs, and generated files unless explicitly needed.
- Define retention and test a restore; a backup that has never been restored is not a verified recovery plan.
- Never store backups under a publicly served directory.

Sources: [Packagist](https://packagist.org/packages/spatie/laravel-backup) · [Documentation](https://spatie.be/docs/laravel-backup)

## Conditional additions — only when a requirement exists

| Package / tool | Consider when | Do not add when |
|---|---|---|
| `laravel/horizon` `^5.50` | Queues move to Redis and operators need a queue dashboard, balancing, and monitoring. | The application stays on the database queue. Horizon is Redis-oriented and requires compatible process extensions/runtime. |
| `laravel/scout` + Meilisearch | Skill/job/catalog search becomes large enough that database search is insufficient. | Current catalog sizes and filters are modest. |
| `spatie/laravel-medialibrary` | Many reusable media collections, conversions, and attachment lifecycle rules are required. | Existing private document paths remain simple and adequate. |
| `laravel/telescope` | The team needs deeper local request/query/job inspection. | Boost, logs, and tests already answer the development question. Keep it off production unless access and retention are secured. |
| `spatie/laravel-pdf` | Reports need a browser-based renderer and the deployment can run its browser/Chrome requirements. | Simple report tables are adequately rendered by DomPDF. Choose one renderer. |
| Playwright or Laravel Dusk | Real browser journeys and visual regression testing become a priority. | Feature tests cover the current server-rendered interactions sufficiently. |
| Redis / Predis or PHP Redis extension | Queue throughput or shared cache/locks justify a Redis service. | A database queue/cache is sufficient for the current project size. |

Horizon compatibility checked: 5.50.1 supports Laravel 13; it requires process extensions and Redis for its intended queue dashboard.  
Source: [Packagist — Laravel Horizon](https://packagist.org/packages/laravel/horizon) · [Laravel 13 Horizon documentation](https://laravel.com/docs/13.x/horizon)

## Use Laravel core instead of adding a package

| Requirement | Use |
|---|---|
| Authentication, sessions, remember-me, password reset, email verification | Laravel authentication and notification features |
| Authorization | Policies, Gates, middleware, and the already-installed Spatie Permission |
| Validation | Form Requests or controller validation |
| Private uploads/downloads | Laravel Filesystem with a private disk and authorized download controller |
| Background processing | Laravel Queue and database queue initially |
| Retry/failure handling | Laravel jobs, failed_jobs, backoff, and queue commands |
| Scheduled maintenance | Laravel Scheduler |
| Caching and throttling | Laravel Cache and RateLimiter |
| Database transactions | Laravel DB transactions |
| Events/notifications | Laravel Events and Notifications |
| Server-rendered UI | Blade; use current Livewire only for targeted interactivity |
| Search at current scale | MySQL indexes and scoped queries |

## Explicitly avoid for the current scope

- **A second AI SDK** such as another OpenAI/Laravel wrapper: `laravel/ai` is already installed. Do not split provider handling between SDKs.
- **A separate tenancy framework** such as a database-per-tenant tenancy package: the current product uses organization-owned rows in one database. Implement and test row-level tenant scoping first.
- **Filament or another full admin framework:** the existing product is Blade-based; adding a second UI architecture would increase integration and styling costs.
- **React/Vue/Next or a SPA rewrite:** there is no requirement that justifies abandoning the existing server-rendered app.
- **A repository/service/DTO package for every model:** use Laravel conventions; introduce abstractions only when a real domain complexity needs them.
- **A second permission system:** finish using the installed Spatie package and Laravel Policies.
- **A search engine before measuring database search:** add Scout/Meilisearch only after observing real performance needs.
- **A PDF package plus browser automation package plus a second PDF engine:** choose one rendering path.

## Runtime baseline recommendation

- Recommended target: PHP 8.4 across local development, CI, Composer, and production.
- The current Composer constraint is `^8.3`, while CI already runs PHP 8.4.
- The latest selected Activitylog and Backup package lines expect PHP 8.4+, so align `composer.json` to `^8.4` only after verifying production hosting.
- Do not let `AGENTS.md`, README, CI, and the actual Composer constraint advertise different PHP versions.

## Controlled installation order

1. Confirm host PHP/extensions and align the PHP baseline.
2. Add Activitylog and tests for sensitive audit events.
3. Add Larastan as development-only and baseline existing findings.
4. Add Excel import/export with one narrow, tested workflow.
5. Add DomPDF with one analysis report.
6. Add Sentry after privacy scrubbing is implemented.
7. Add Backup only after verifying database dump and remote storage access.
8. Add Horizon only if Redis is actually introduced.

For every addition:

```powershell
composer require vendor/package
composer audit
php artisan test --compact
npm run build
```

For development-only tooling:

```powershell
composer require --dev vendor/package
composer audit
php artisan test --compact
```

Always review `composer.json`, `composer.lock`, the dependency tree, and the package's active security advisories before merging.

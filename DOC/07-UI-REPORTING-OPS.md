# 07 — UI, Reporting, Deployment, and Team Operations

## UI direction

Keep the existing Blade + Tailwind UI. The current design already uses a dark sidebar, light workspace, shared layout, and reusable field/action classes. Improve it consistently rather than replacing it with another frontend stack.

### Navigation and information architecture
- Keep navigation grouped by Overview, Academic, Skills, Labor Market, Student, Analysis, and Administration.
- Show links based on permissions, not only role names.
- Add breadcrumbs and consistent page titles.
- Keep destructive actions visually distinct and require confirmation.
- Add a clear current organization/workspace indicator.
- Show the user's role and account state in the profile menu.

### Page standards
Every list page should have:
- Search and relevant filters.
- Server-side pagination.
- Stable sort order.
- A useful empty state and clear call to action.
- Loading/success/error/validation states.
- A mobile-friendly table/card presentation.
- Authorization-safe detail links.

Every detail page should have:
- Summary and status.
- Related data.
- Source/evidence and history.
- Available actions for the current permission.
- Created/updated/reviewed actor and timestamps where relevant.

### Forms
- Use clear labels, help text, and units.
- Show field-level validation errors.
- Preserve old values after validation errors.
- Prevent double submission for expensive operations.
- Explain whether an action is immediate, queued, or pending review.
- Make required/preferred, required/elective, verified/unverified states visually distinct.

### Accessibility and localization
- Use semantic headings, labels, buttons, and table headers.
- Support keyboard focus and visible focus states.
- Do not rely on color alone to communicate status.
- Ensure contrast and readable text sizes.
- Decide the required application languages before adding localization work.
- If Arabic is included, support RTL layout throughout navigation, forms, tables, charts, dates, emails, and PDF reports. Test Arabic fonts and shaping in PDF generation.

## Reporting features

### Curriculum alignment report
Include:
- Organization, department, program, curriculum version, and academic year.
- Target job profile and its source/version.
- Overall alignment and methodology version.
- Course count and curriculum weight.
- Per-skill required level, covered level, gap, importance, evidence, and contributing courses.
- Missing approved mapping warnings.
- Generation date and requesting user.

### Student readiness report
Include:
- Student profile information limited to authorized users.
- Target job profile and source.
- Alignment score and assessed coverage as separate values.
- Verified assessment level, required level, gap, evidence, assessor, and assessment date.
- Skills not assessed.
- Recommendations and their approval/status.
- Clear privacy notice for downloaded reports.

### Comparison reports
- Compare two curriculum versions against the same job profile.
- Compare student analysis runs for the same target role.
- Show methodology version and input differences.
- Never compare scores calculated under incompatible methodologies without warning.

## Excel/CSV workflow

Use the planned Laravel Excel package only after the core data model and policies are stable.

### Import flow
1. Download a template with documented columns and sample rows.
2. Upload the file to private temporary storage.
3. Validate extension, MIME, size, row count, headers, and allowed values.
4. Preview parsed rows and errors.
5. Resolve duplicates and canonical skill matches.
6. Confirm the import.
7. Process large imports in a queued job.
8. Persist an import summary and row-level result.
9. Delete temporary source files according to retention policy.

### Export flow
- Export only records the user is authorized to read.
- Apply the active page filters and sort rules.
- Protect CSV exports from spreadsheet formula injection.
- Queue large exports and offer time-limited authorized downloads.
- Log sensitive exports.

## PDF report workflow

Use the planned DomPDF package for the first report implementation.

- Render a dedicated Blade report view.
- Keep remote file fetching disabled unless allow-listed.
- Restrict reports to scoped records.
- Include methodology and data provenance.
- Test long tables, page breaks, fonts, and malicious content.
- Generate from a persisted analysis run so results remain historically stable.
- Consider browser-based PDF rendering only if a real layout requirement cannot be met by DomPDF.

## Background processing and operations

### Initial queue setup
- Keep the database queue until actual throughput requires Redis.
- Run a supervised queue worker in production.
- Use explicit tries, backoff, timeout, and failure states.
- Monitor `failed_jobs` and document retry procedures.
- Make jobs idempotent when possible.
- Ensure temporary files are cleaned after success/failure.

### Redis and Horizon
Only introduce Redis and Horizon when:
- Queue volume or latency requires it.
- The deployment supports Redis and the required PHP process extensions.
- A monitoring/operations owner is assigned.
- The team can manage Redis backups, availability, and credentials.

### Scheduler
Use Laravel Scheduler for:
- backup and backup health checks;
- stale temporary-file cleanup;
- expiring old exports;
- stale/expired evidence review alerts;
- scheduled report generation if approved;
- periodic queue/failed-job checks.

Every scheduled command should be safe to run more than once.

## Deployment checklist

### Environment
- PHP 8.4 target, after confirming host support.
- MySQL 8+ with least-privilege credentials.
- Node 22 only in the build pipeline; production needs built assets, not necessarily a Node server.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- Correct `APP_URL`, timezone, session cookie settings, and trusted proxies.
- Private storage disk for student and syllabus files.
- Queue connection and worker configured.
- AI provider/model/key set only where live AI is enabled.
- Mail transport configured for verification/reset and notifications.

### Deployment sequence
1. Build and test in CI.
2. Back up database and private files.
3. Deploy the reviewed code and lock files.
4. Install dependencies from lock file.
5. Build frontend assets.
6. Run `php artisan migrate --force`.
7. Clear/rebuild config, route, and view caches as appropriate.
8. Restart queue workers.
9. Verify health endpoint, login, private file access, queue processing, and a sample analysis.
10. Record the deployed commit and rollback plan.

Do not run destructive migrations or database resets against production.

### Backup and recovery
- Store backups separately from the application and outside the public document root.
- Encrypt sensitive backup archives when possible.
- Keep a documented retention schedule.
- Back up both the database and private uploaded evidence.
- Test restore into an isolated environment.
- Verify uploaded documents are accessible after restore and remain private.
- Define recovery point and recovery time objectives appropriate to the graduation project.

### Monitoring
- Keep structured application logs.
- Add Sentry only after PII/secret scrubbing is tested.
- Monitor queue age, failed jobs, AI provider errors, storage capacity, and database capacity.
- Provide a way to disable live AI processing without disabling deterministic analysis.
- Never expose stack traces or provider credentials to end users.

## Team workflow for six or more contributors

Suggested ownership:

| Workstream | Responsibility |
|---|---|
| Integration lead | Scope, architecture, code review, release decisions |
| Academic domain | Departments, programs, courses, curriculum versions, outcomes |
| Skills and labor market | Skill taxonomy, aliases, occupations, job profiles, requirements |
| AI and document processing | AI agents, extraction jobs, schema validation, human review |
| Student domain | Student accounts, assessments, projects, certificates |
| Analysis and reporting | Scoring, snapshots, recommendations, PDF/Excel outputs |
| UI and QA | Blade screens, accessibility, feature tests, regression testing |

Roles may be combined depending on the actual team size, but every domain must have one accountable owner.

### Branch and PR rules
- `main` is the stable integration branch.
- Use small feature branches named by domain, such as `feature/manual-skill-mapping`.
- Do not commit directly to `main) during normal work.
- Each PR should state scope, behavior changed, migration notes, authorization impact, screenshots where relevant, and tests run.
- Coordinate migration names and shared contracts through the integration lead.
- Do not let two contributors independently change the scoring formula or shared status vocabulary.
- Review every dependency addition and migration.
- Delete temporary test data and never commit production exports.

## Demo readiness

A credible graduation demo should use a prepared synthetic scenario:

1. Create an institution, department, and program.
2. Add a curriculum version with multiple semesters.
3. Add courses and measurable learning outcomes.
4. Create or import a target job profile.
5. Add manual approved mappings or upload a sample syllabus for AI suggestions and human review.
6. Run curriculum alignment and explain at least one gap.
7. Create a student, submit self-assessment and project evidence.
8. Verify selected assessments as an instructor.
9. Run student readiness and show assessed coverage separately.
10. Assign a recommendation and update its status.
11. Export a PDF report and show its methodology and evidence.

Prepare a deterministic no-AI path as the fallback. Live AI should be demonstrated only when the provider is configured and the sample files are cleared for provider processing.

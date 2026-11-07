# 06 — Security, Privacy, Testing, and CI

## Security baseline

### Authentication and authorization
- Keep Laravel authentication and Spatie Permission as the shared role/permission source.
- Define permission names as action capabilities; define Policies for record ownership.
- Apply authorization to view, create, update, delete, approve, verify, export, and download.
- Do not rely on hidden UI links as security.
- Prevent role self-assignment and privilege escalation.
- Recheck account activation and organization membership for queued actions where relevant.

### Tenant isolation
- Add tests for cross-organization IDs on every tenant-owned route.
- Test nested resources, exports, file downloads, and queued work.
- Ensure a user cannot access another organization's document by guessing the document ID.
- Ensure shared catalog reads and writes follow the agreed global-data policy.
- Keep background jobs tenant-safe and idempotent.

### File security
- Store syllabus and certificate uploads on a private disk.
- Never expose direct public storage URLs for private records.
- Use random server-generated file paths; retain the original filename only as metadata.
- Validate extension, MIME/content, size, and actual parser behavior.
- Add malware scanning when supported by the deployment environment and required by institutional policy.
- Authorize every preview/download/delete request.
- Define retention and deletion behavior.
- Ensure backups containing private files are also protected.

### AI and secrets
- Keep provider keys in server-side environment secrets.
- Never expose keys in Vite variables, HTML, JavaScript, logs, screenshots, or committed files.
- Record explicit consent before submitting syllabus content to a provider.
- Minimize personal/student information in prompts.
- Redact provider exceptions and request metadata before showing them to users.
- Add request throttling and quotas for expensive processing.

### Application hardening
- Production: `APP_DEBUG=false`, HTTPS, secure session cookies, strict trusted-host/proxy configuration, and least-privilege database credentials.
- Use CSRF protection for state-changing web forms.
- Keep login and expensive AI/upload routes throttled.
- Validate URLs and restrict remote resource fetching in PDF generation.
- Run `composer audit` and review GitHub security alerts.
- Keep dependencies and GitHub Actions updated.
- Do not commit real student data, uploaded syllabus PDFs, database dumps, or secrets.

## Test strategy

### Unit tests
- `GapScoreCalculator`: boundary values, invalid values, missing skills, weights, caps, thresholds.
- Curriculum coverage calculation and elective weighting.
- Canonical skill/alias normalization.
- Recommendation priority rules.
- Status transition rules.

### Feature tests
- Registration and login, inactive accounts, verification, password reset.
- Every role/permission combination for important actions.
- Organization A cannot read/write Organization B's academic, job, document, student, assessment, analysis, recommendation, or export data.
- Course and curriculum validation, course semester membership, publication, and cloning.
- Manual mapping create/edit/approve/reject.
- Syllabus upload consent, private storage, duplicate hash, queue dispatch, retries, failed state, approval/rejection.
- Job-posting extraction and human review.
- Student assessment submission, verification, rejection, project review, certificate workflow.
- Student analysis and curriculum analysis with no data, partial data, and complete data.
- Snapshot immutability after source records change.
- Recommendation assignment and status transitions.
- PDF/Excel exports and tenant scoping.

### AI job tests
- Fake or mock the Laravel AI provider boundary.
- Test valid structured output and malformed/missing fields.
- Test invalid IDs, unknown skills, low confidence, invalid page numbers, and duplicate output.
- Test provider timeout, rate limit, and retry.
- Ensure AI output cannot approve itself or directly alter official mappings.

### Browser / UI tests
- Start with server-side feature tests.
- Add Playwright or Laravel Dusk only for journeys that require real browser behavior, such as review modals, uploads, and complex dynamic forms.
- Cover the primary demo flows on desktop and mobile widths.
- Test keyboard navigation and form error messages.

## CI improvements

### Current observation
The existing workflow runs `composer update`, tests, npm install/build, and can automatically push a refreshed `composer.lock` to `main`. The branch is currently unprotected. Automated tests use SQLite in-memory.

### Target pipeline
1. Checkout.
2. Set up the agreed PHP version and required extensions.
3. Run `composer install --prefer-dist --no-interaction` from the committed lock file.
4. Run `composer audit`.
5. Run Pint in check mode.
6. Run Larastan after its baseline is agreed.
7. Run Pest on SQLite for fast tests.
8. Run an integration test job against MySQL 8.
9. Set up Node 22 and run `npm ci`.
10. Run `npm run build`.
11. Upload test/build artifacts where useful.
12. Require successful checks before merge.

### Workflow rules
- Do not run `composer update` on every push.
- Do not let a GitHub Action push dependency changes directly to `main`.
- Dependency updates should open a PR and be reviewed.
- Use least-privilege workflow permissions; give write access only to jobs that need it.
- Pin third-party GitHub Actions to reviewed versions or commit SHAs according to the team's security policy.
- Configure Dependabot for Composer, npm, and GitHub Actions.
- Protect `main) with PR review and required CI checks.
- Keep secrets unavailable to untrusted fork PR workflows.

## Test data and coverage

- Use factories and seeders for synthetic organizations and domain records.
- Include at least two organizations in tenant-isolation tests.
- Include shared/global skills that are visible to both organizations but can only be modified by authorized roles.
- Create scoring fixtures with hand-calculated expected values.
- Avoid seeding a real student or real institution's personal information.
- Track coverage as a diagnostic metric, not as a substitute for meaningful assertions.

## Release gates

A release is blocked when:
- migrations fail on an empty MySQL database;
- tenant-isolation tests fail;
- a private file is reachable without authorization;
- deterministic scoring differs from approved benchmark results;
- a failed AI job corrupts official data;
- a past analysis changes after source edits;
- composer audit reports an unresolved high/critical advisory without an approved exception;
- production build or required tests fail.

## Incident and privacy response

- Define who can access production logs and backups.
- Record how to disable AI processing if a provider key or endpoint is compromised.
- Define a way to revoke user access and invalidate sessions.
- Define backup restore and document deletion procedures.
- Keep incident notes free of unnecessary student data.

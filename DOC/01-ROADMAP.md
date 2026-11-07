# 01 — Product and Engineering Roadmap

## Delivery strategy

Improve the existing application through vertical slices. Avoid a large rewrite or a long sequence of infrastructure work that produces no visible product value.

Priority definitions:

- **P0 — Foundation:** required before trusting the system with real institutional data or expanding the team.
- **P1 — Core product:** required for a credible end-to-end curriculum-to-job and student-to-job demonstration.
- **P2 — Product completeness:** important workflows, reporting, and usability.
- **P3 — Advanced capability:** scale, external integrations, and research-grade evaluation.

## Phase 0 — Stabilize the repository (P0)

### Work
- Standardize the runtime contract. Recommended project baseline: PHP 8.4, matching the existing CI runtime and enabling the selected maintenance packages. Update the Composer PHP constraint only after confirming the target hosting runtime.
- Keep Laravel 13 and the current Blade/Tailwind/Vite stack.
- Change CI dependency installation to `composer install --prefer-dist --no-interaction`; do not run `composer update` on every push.
- Remove automatic lock-file commits from CI. Dependency updates should be intentional developer changes reviewed in a PR.
- Protect `main`; require PR review and green CI before merge.
- Add Dependabot or an equivalent scheduled dependency review.
- Make CI test both fast SQLite tests and a MySQL 8 service job for migration/query compatibility.
- Keep the existing Composer lock file committed and reproducible.
- Remove or correct inconsistent PHP-version guidance in project instructions.

### Acceptance criteria
- A clean clone installs from the committed lock file.
- Migrations run on an empty MySQL 8 database.
- Automated tests and Vite build run in CI.
- CI never writes commits to `main`.
- A contributor cannot merge a failing PR without an explicit repository-owner override.

## Phase 1 — Identity, permissions, and organization isolation (P0)

### Work
- Write down a role-permission matrix for `super-admin`, `university-admin`, `department-admin`, `instructor`, `career-advisor`, `employer`, `student`, and `graduate`.
- Use named permissions for actions and Policies for record-level ownership. Roles should grant permissions; controllers should not duplicate access rules.
- Apply tenant scoping to every organization-owned query, lookup, export, file download, and queued job.
- Decide whether public organization registration remains enabled. For a real institutional deployment, prefer invitations or an administrator-approved onboarding flow.
- Add email verification and password-reset flows using Laravel's built-in authentication features.
- Add account activation/deactivation and safe account lifecycle handling.
- Restrict global taxonomy administration to explicitly authorized roles.
- Add an audit trail for sensitive actions: user/role changes, course/curriculum edits, mapping approvals, job requirement approvals, assessment verification, and exports.

### Acceptance criteria
- Cross-organization reads and writes return 404/403 consistently.
- A user cannot grant themselves a role or permission.
- Every sensitive state change has actor, timestamp, subject, and meaningful context recorded.
- Guest, inactive, unverified, and unauthorized account behavior is tested.

## Phase 2 — Complete the academic workflow (P1)

### Work
- Add complete Department, Program, Course, and Curriculum Version management.
- Support course membership fields: semester, position, required/elective, and any curriculum-specific weight.
- Support draft, review, published, and archived curriculum states.
- Add safe version cloning so a new version does not overwrite historical curricula.
- Add manual course-to-skill mapping with level, evidence, confidence, source, reviewer, and approval state.
- Add learning outcome create/edit/review. Preserve evidence, page/source reference, and reviewer decision.
- Add outcome-to-skill mapping review if this model remains part of the methodology.
- Connect academic terms, course assessments, and assessment weights to the UI and calculations, or explicitly defer/remove unused paths after team approval.
- Validate that course/program/curriculum relationships all belong to the authenticated organization.
- Add a preview of the curriculum before publication and prevent accidental editing of a published version.

### Acceptance criteria
- An instructor can create a course, add outcomes, add skills manually, and submit mappings for approval without AI.
- A reviewer can approve or reject each outcome and mapping independently.
- A curriculum can contain multiple semesters and elective courses.
- A published curriculum version is immutable; changes create a new version.
- Historical analysis can still be opened after a later curriculum version is published.

## Phase 3 — Complete labor-market data management (P1)

### Work
- Improve occupation and job-profile CRUD, including search, filters, status, source, country, and seniority.
- Add profile review/approval and profile versioning or a clear change-history policy.
- Make required/preferred requirements meaningful in the analysis methodology. Do not silently ignore `is_required`.
- Define how AI confidence affects review ordering; never auto-approve based only on a threshold.
- Add a canonical skill matching flow using normalized names and aliases before creating new skills.
- Add reviewed import templates for occupation/job-profile/requirement datasets.
- Record external source, version/date, license, and import provenance for ESCO/O*NET or local datasets.
- Complete employer workflows only if the graduation scope actually needs employer accounts.
- Add duplicate detection and safe re-import behavior.

### Acceptance criteria
- A career advisor can create or import a job profile with source provenance.
- Each skill requirement has level, weight, required/preferred state, and evidence.
- AI-extracted job skills remain pending until approved.
- Re-importing the same source does not duplicate occupations, profiles, or skills.

## Phase 4 — Complete student evidence and portfolio (P1)

### Work
- Complete student account/profile administration and enrollment/program association.
- Add student project review: pending, approved, rejected, needs changes; include reviewer notes and timestamps.
- Add certificate upload/verification with private storage and access control.
- Keep self-assessment separate from verified assessment.
- Allow authorized assessors to verify, reject, or request evidence changes.
- Add assessment rubrics and evidence types so a level is supported by observable work.
- Define whether reviewed projects/certificates can support assessments. They must not automatically increase a student's level.
- Add learner-facing history and clear feedback on why an assessment is or is not used in analysis.

### Acceptance criteria
- Students can maintain a portfolio and submit evidence.
- Reviewers can approve/reject with traceable notes.
- Analysis uses only verified evidence according to the approved methodology.
- A student cannot inspect another student's private evidence.

## Phase 5 — Complete analysis and recommendations (P1)

### Work
- Preserve the current deterministic calculation service and write a benchmark suite before modifying formulas.
- Create documented policies for preferred requirements, missing mappings, repeated evidence, course credit weighting, elective weighting, and unassessed student skills.
- Expand analysis snapshots to preserve the exact inputs that affect results, including approved mapping/requirement values and source references.
- Improve report breakdowns: overall alignment, assessed coverage, each skill gap, importance, evidence, source, and confidence/review status.
- Add recommendation management: assign, accept, reject, mark in progress, complete, due date, and reviewer notes.
- Separate rule-based recommendations from AI drafts in the UI.
- Add re-analysis comparison between curriculum versions and between analysis runs.
- Add an explainability view showing how each score was calculated.

### Acceptance criteria
- All calculations match a set of hand-calculated benchmark cases.
- A past report never changes when an input record is edited later.
- A missing student assessment is shown as not assessed, not as a proficiency score of zero.
- Users can see what evidence caused every result and recommendation.

## Phase 6 — Reporting, imports, and UX (P2)

### Work
- Add downloadable PDF analysis reports.
- Add Excel/CSV imports for courses, students, skills/aliases, occupations, and job requirements with preview and validation.
- Add Excel/CSV exports for selected lists and analysis results.
- Add filters, server-side pagination, sorting, saved filter state, and clear empty states.
- Improve dashboard cards and trend comparisons without presenting illustrative data as real.
- Add report metadata: institution, program, curriculum version, target role, generated date, methodology version, and filters.
- Improve responsive layouts, accessibility, keyboard navigation, and error messages.
- Decide whether Arabic/RTL is required; if yes, implement proper localization and test PDF fonts rather than simply translating labels.

### Acceptance criteria
- Imports validate and preview rows before writing data.
- A failed import reports row-level errors and does not leave partial inconsistent data.
- Reports are scoped to the user's organization.
- PDF and Excel exports have automated tests and stable output fields.

## Phase 7 — AI quality and research evaluation (P2/P3)

### Work
- Version prompts and structured-output schemas.
- Maintain a manually reviewed gold dataset of sample syllabi and job descriptions.
- Compare AI extractions with expert annotations using precision, recall, and F1.
- Track per-field quality, false positives, duplicates, confidence calibration, and reviewer acceptance rate.
- Add queue observability, retries, idempotency, timeouts, provider error classification, and cost/usage tracking where provider data is available.
- Add model/provider configuration per environment; keys remain server-side.
- Add safe file retention/deletion and consent records.
- Evaluate retrieval or external taxonomy matching only after canonical skill data and gold labels are stable.

### Acceptance criteria
- The demo can run with deterministic sample data and no AI key.
- Live AI is an optional, clearly configured capability.
- No accuracy claim is made without measured results against expert labels.
- Re-processing a document does not silently duplicate approved mappings.

## Phase 8 — Production readiness (P2/P3)

### Work
- Production environment checklist, HTTPS, secure cookies, debug disabled, least-privilege DB user, and private file storage.
- Supervised queue workers and scheduled tasks.
- Database and private-file backup with restore drills.
- Error monitoring and log redaction.
- Dependency audit and security review before releases.
- Document deployment/rollback and database migration procedure.

### Acceptance criteria
- A documented restore procedure has been tested.
- Failed jobs are visible and recoverable.
- Production secrets and private student documents are not present in Git.
- Release rollback and migration safety are documented.

## Recommended implementation order

1. Phase 0 — stabilize CI and dependency workflow.
2. Phase 1 — organization isolation and permissions.
3. Phase 2 — complete academic mapping workflow.
4. Phase 3 — job profile and requirement workflow.
5. Phase 4 — student verification workflow.
6. Phase 5 — analysis and recommendations.
7. Phase 6 — reporting and user experience.
8. Phase 7 — AI evaluation and improvements.
9. Phase 8 — production readiness.

## Vertical-slice rule

Each PR should deliver one coherent user outcome, for example:

- Manual mapping: migration (if needed) → policy → controller/action → UI → tests → audit event.
- Project review: policy → review action → state transition → reviewer notes → UI → feature tests.
- Analysis report: query/snapshot → calculation display → PDF export → tenant tests → export tests.

Avoid PRs that only add model files or packages without a usable workflow.

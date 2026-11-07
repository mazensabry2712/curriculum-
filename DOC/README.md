# CSGA — Project Improvement Documentation

> **Document set:** Product, architecture, engineering, AI governance, quality, and delivery plan  
> **Repository:** `mazensabry2712/curriculum-`  
> **Baseline reviewed:** `main` at `9478ec0f15f0ac7d988efeb676653ccb6c573f6f`  
> **Review date:** 2026-11-07  
> **Status:** Planning document only. No application code or dependencies are changed by this document set.

## Purpose

This folder is the shared implementation reference for improving the existing Curriculum Skills Gap Analyzer (CSGA). It is based on the code and schema present in the repository, not on a greenfield rewrite.

The goal is to turn the current Laravel foundation into a reliable, explainable, secure, demonstrable graduation project while keeping the stack understandable for a team of six or more contributors.

## Product direction

CSGA connects three evidence sources:

1. **Academic curriculum:** what a program intends to teach, represented by versioned curricula, courses, learning outcomes, approved skill mappings, and assessment evidence.
2. **Labor-market demand:** what a target occupation or job profile requires, represented by reviewed skill requirements with proficiency levels and importance weights.
3. **Student readiness:** what an individual learner can demonstrate, represented by verified assessments and reviewed portfolio evidence.

The analysis engine compares these sources and produces explainable gap results and practical recommendations. AI assists with extraction and drafting; it must never silently approve academic facts or become the source of the official score.

## Documentation map

| File | Purpose |
|---|---|
| [01 — Roadmap](01-ROADMAP.md) | Phases, priorities, dependencies, and completion criteria |
| [02 — Packages](02-PACKAGES.md) | Existing packages, approved candidates, optional tools, and packages we should avoid |
| [03 — Architecture and Data](03-ARCHITECTURE-DATA.md) | Current architecture, tenancy, schema improvements, and data lifecycle |
| [04 — Modules](04-MODULES.md) | Detailed improvements by product module |
| [05 — AI and Analytics](05-AI-ANALYTICS.md) | AI pipelines, review rules, scoring methodology, evaluation, and recommendation design |
| [06 — Security and Quality](06-SECURITY-QUALITY.md) | Authorization, privacy, tests, CI, and release gates |
| [07 — UI, Reporting, and Operations](07-UI-REPORTING-OPS.md) | UX, reports, imports/exports, deployment, monitoring, backups, and team workflow |

## How to use this folder

- Treat **P0** items as blockers or foundational decisions.
- Deliver work in small vertical slices; each feature must include its migration, authorization, UI, tests, and documentation updates where relevant.
- Do not install every package listed here. The package document distinguishes selected candidates from conditional options and intentionally excluded dependencies.
- Before adding a dependency, check the current installed PHP/Laravel versions, package compatibility, active security advisories, maintenance health, and hosting requirements.
- Keep `main` stable. Implement work on feature branches and use reviewed pull requests.
- Do not rewrite working modules just to match a preferred pattern. Improve the existing implementation incrementally.
- Do not send private syllabi, student personal data, provider keys, or production records to GitHub or public AI prompts.

## Current baseline — verified in the repository

### Stack

- PHP constraint: `^8.3`; CI currently runs PHP 8.4.
- Laravel: `^13.17`.
- Laravel AI SDK: `^0.11.2`.
- Livewire: `^4.0` is installed, but the current screens are Blade views; no Livewire components were found in the reviewed tree.
- Spatie Laravel Permission: `^8.3`.
- UI: Blade, Tailwind CSS 4, Vite 8.
- Database target: MySQL 8+; automated tests currently use SQLite in-memory.
- Queues: database queue by default.
- Tests: Pest 5.
- The current repository has academic, skills, labor-market, student, AI, analysis, and dashboard code.

### Implemented foundations

- Organization registration and role assignment.
- Academic organizations, departments, programs, courses, and curriculum versions.
- Shared skill catalog, categories, and aliases.
- Private syllabus PDF upload with consent and queued AI extraction.
- Human review of syllabus skill suggestions.
- Occupations, job profiles, job postings, and queued AI extraction of job skills.
- Student profiles, self-assessments, verified assessments, career targets, and project submission.
- Deterministic curriculum/student alignment calculations and saved analysis snapshots.
- Rule-based recommendations and optional queued AI recommendation drafts.
- Initial dashboards and Blade screens.

### Important gaps confirmed during review

- Course/semester/elective membership is not yet fully manageable: curriculum creation currently places selected courses in semester 1 and marks them required.
- There is no complete manual course-to-skill mapping workflow.
- Learning outcomes are created from the latest AI output during skill approval; their evidence is not persisted in the current approval path, and each outcome does not have its own review decision.
- Permission records are seeded, but route protection primarily checks roles; the permission matrix is not consistently used as the authorization source.
- Student project review is incomplete; certificates exist in the schema but have no complete UI/controller workflow.
- Recommendation assignment, due dates, status transitions, and follow-up screens are incomplete.
- Academic terms, course assessments, outcome-skill mappings, external skill frameworks/mappings, and employer records are not all connected to complete user workflows.
- Tests cover only a small part of the core domain and do not yet prove cross-organization isolation or full end-to-end flows.
- CI currently runs `composer update` and can push a refreshed lock file directly to `main`; the branch is not protected.
- The application uses Blade web routes; a REST API is not currently part of the architecture.

## Guiding engineering decisions

1. Keep Laravel as a modular monolith.
2. Keep Blade as the primary UI. Use the already-installed Livewire only where it materially improves a complex interactive workflow.
3. Keep the deterministic scoring engine separate from AI services.
4. Use organization scoping and Policies/Gates for tenant authorization.
5. Preserve immutable analysis history and explain every displayed score.
6. Prefer Laravel's built-in features before adding a package.
7. Add packages only for concrete needs: audit history, Excel import/export, PDF reports, static analysis, error monitoring, and backups.
8. Do not introduce a SPA, a second AI SDK, a separate tenancy framework, or an admin-panel framework without a specific approved requirement.

## Definition of done for a feature

A feature is not complete when its page merely renders. It is complete when:

- The user story and acceptance criteria are met.
- Data ownership is enforced server-side.
- Input is validated and state transitions are guarded.
- The happy path and important failure paths have automated tests.
- Queue work exposes meaningful states and safe errors.
- Private files remain private.
- Empty, loading, success, validation, and failure states are understandable.
- The UI works at desktop and mobile widths and supports keyboard navigation.
- The database migration can run from an empty database and roll back safely.
- The feature is demonstrated with synthetic data.

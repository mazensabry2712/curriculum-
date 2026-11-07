# 03 — Architecture and Data Model Improvement Plan

## Target architecture

Keep a Laravel modular monolith. The domains should remain clear without splitting the app into services or introducing an API gateway.

Recommended logical boundaries:

- Identity and organization tenancy
- Academic structure and curriculum
- Skill taxonomy and evidence mappings
- Labor market and job profiles
- Student portfolio and verified evidence
- AI extraction and review
- Deterministic analysis and snapshots
- Recommendations and reporting
- Audit and operations

Controllers should coordinate HTTP input/output. Domain calculations should remain in services. Authorization should be expressed through Policies/Gates and organization scoping. Queue jobs should call domain services rather than owning long business rules.

## Current application shape

- Web entry point: `routes/web.php`.
- UI: Blade views under `resources/views`.
- Styles/build: Tailwind CSS 4 and Vite.
- Domain logic: models, controllers, jobs, AI agents, and `app/Services/Analysis`.
- Storage: syllabus documents are saved on a private local disk by default.
- AI: Laravel AI SDK agents use structured output.
- Background work: database queue by default.

No REST API is currently present. Do not introduce API Resources, API versioning, Sanctum, or an SPA unless a separate mobile/external-client requirement is approved.

## Organization tenancy

### Ownership categories

| Data | Ownership |
|---|---|
| Organization, department, program, course, curriculum, students, documents, analysis runs | Organization-owned |
| Job profiles | Shared global profiles or organization-owned profiles |
| Skills, categories, aliases, occupations, frameworks | Shared catalog, with controlled administration |
| Student assessments, projects, certificates, targets | Owned by a student profile within one organization |
| AI runs and review records | Inherit access from their source document/posting/analysis |

### Required rules

- Never accept `organization_id` from a request as proof of access.
- Resolve organization identity from the authenticated user's account.
- Scope every tenant-owned route binding and nested relation.
- Scope exports, file downloads, search results, dashboard counts, queues, and report generation.
- When a queued job receives a record ID, reload the record and derive ownership from the persisted source record; do not trust a client-supplied tenant ID.
- Global/shared catalog writes must be restricted to authorized catalog managers.
- Consider a global catalog contribution workflow if organizations need to suggest new skills without immediately changing the shared catalog.

### Policy design

Create Policies for at least:

- Organization
- Department / Program / Course / CurriculumVersion
- Document and document suggestions
- JobProfile / JobPosting / JobSkillExtraction
- StudentProfile / Assessment / Project / Certificate
- AnalysisRun / Recommendation
- Export and audit-log access

A role decides what action a user may attempt; a Policy decides whether the user may perform it on this particular record.

## Data-model improvement checklist

### Academic domain

- Curriculum version lifecycle: draft → in_review → published → archived.
- Add explicit publish metadata: published_by and published_at.
- Once published, protect the version from direct mutation; clone into a new draft.
- Add semester and course position editing to curriculum membership.
- Clarify the difference between `is_required` and `is_elective`; enforce that invalid combinations cannot be saved.
- Add unique/foreign-key constraints for all mapping relationships.
- Preserve outcome evidence and source page/reference.
- Give each outcome its own review status and reviewer metadata.
- Connect `curriculum_assessments` to course outcomes and the methodology, or defer it explicitly.
- Connect `academic_terms` to the intended scheduling/academic-year workflow, or defer it explicitly.
- Decide whether course-skill mapping is the scoring source or whether outcome-skill mappings are authoritative. Avoid two competing truth sources.

### Skill taxonomy

- Keep a stable canonical skill ID and unique normalized aliases.
- Normalize case, whitespace, punctuation, and known synonyms consistently.
- Before creating a skill from AI output, attempt alias/canonical matching and present a possible match to the reviewer.
- Record category/type and provenance for new skills.
- Add merge/redirect handling for duplicate canonical skills; do not silently delete used skills.
- Keep external framework mappings versioned and attributable to a source import.

### Labor market

- Every job requirement should identify its evidence source and reviewer.
- Add version/history rules for job profiles when their requirements change.
- Enforce `required_level` and `importance_weight` ranges at application and database levels where supported.
- Define how preferred requirements affect scores.
- Preserve original posting content and source provenance separately from approved job-profile requirements.
- Re-imports should be idempotent.

### Student evidence

- Store evidence type, evidence reference, verifier, verified timestamp, and reviewer notes.
- Add a project review lifecycle and certificate verification lifecycle.
- Keep submitted evidence separate from verified skill level.
- Protect uploaded certificates with private storage and an authorization check on each download.
- Avoid using project skill tags as verified proficiency without an explicit assessment.

### AI and analysis records

- AI runs should record prompt/schema version and input/source identifiers.
- Keep provider errors safe and bounded; never display raw secrets or stack traces.
- Add idempotency or source-run identity to avoid duplicate suggestions after retries.
- Store the reviewed/approved state and reviewer details on individual suggestions.
- Preserve a full immutable analysis input snapshot, including the actual values used for each calculation.
- Consider storing the methodology version on every analysis run so future formula changes are explainable.
- Do not cascade-delete historical analysis evidence when an academic or job profile is retired.

## Data integrity and database conventions

- Use foreign keys and unique indexes for relationship invariants.
- Add indexes after checking real query patterns; avoid indexing every column.
- Add composite indexes for tenant-scoped lists, such as organization plus status/date.
- Use decimal columns with documented precision for proficiency, weights, confidence, and scores.
- Use backed enums or centralized constants for lifecycle/status values once all callers are identified.
- Use explicit transactions for multi-record approvals and imports.
- Keep migration files immutable after they have been applied outside local development; add a new migration to change schema.
- Test both migration from an empty database and rollback behavior.

## Current code findings that should drive the next changes

1. Curriculum creation currently creates every selected course as semester 1 and required. The membership editor is a core missing workflow.
2. `DocumentController::approve` creates learning outcomes from the latest run as part of skill approval. Outcome review and evidence preservation need a separate workflow.
3. The current mapping workflow is primarily AI-driven; add a manual mapping path.
4. `StudentProject` exists and students can submit projects, but a complete reviewer action workflow is missing.
5. `StudentCertificate` exists in the schema and model, but a complete upload/review workflow is missing.
6. `Recommendation` has fields for assignment, due date, and status, but not a complete management workflow.
7. `academic_terms`, `curriculum_assessments`, `outcome_skill_mappings`, and external framework mapping records need an explicit implemented/deferred decision.
8. Current routes use role middleware; the seeded permission matrix should become the consistent permission source, with Policies for tenant ownership.

## Suggested directory evolution

Do not move everything in one refactor. Keep current paths stable while adding focused classes as features are implemented.

Possible future organization:

```text
app/
  Actions/
    Academic/
    Analysis/
    Documents/
    Students/
  Policies/
  Services/
    Analysis/
    Imports/
    Reports/
    Skills/
  Enums/
  Jobs/
  Ai/
    Agents/
  Models/
```

Only add an Action, Enum, or service when it represents real reusable business behavior. Do not create empty folders or wrappers around simple Eloquent operations.

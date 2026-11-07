# 04 — Module-by-Module Product Improvements

## 1. Authentication and user administration

### Current
- Organization registration creates an organization and a university-admin account.
- Login and logout exist.
- Account activation is checked during login.
- Roles are assigned using Spatie Permission.
- Login throttling exists.

### Improve
- Decide the public registration policy: public self-service workspace creation for demo, or invite/approval for institutional use.
- Add email verification and password-reset screens/actions.
- Add invitation flow for university staff and students.
- Add user list, search, role assignment, activation/deactivation, and access review for authorized administrators.
- Prevent privilege escalation and protect the final active super administrator.
- Display role and organization context in the account UI.
- Use named permissions and Policies instead of relying only on route role strings.

## 2. Dashboard

### Current
- Shows course, skill, student, job-profile, and analysis counts.
- Shows recent analysis runs and a document review queue count.

### Improve
- Add date range and program/department filters.
- Show real operational indicators: pending document reviews, pending job extractions, pending assessment reviews, pending project/certificate reviews, active recommendations, and failed jobs.
- Add trend comparisons only when historical data supports them.
- Use organization-scoped counts consistently; clearly mark shared global taxonomy counts.
- Add useful empty states with links to the next action.
- Do not use mock/illustrative scores in authenticated production views.

## 3. Academic structure and curriculum

### Current
- Organization, department, program, course, and curriculum version models/tables exist.
- Basic create actions exist.
- Curriculum creation associates selected courses.

### Improve
- Complete edit/archive actions and detailed views.
- Add semester/position/elective membership management.
- Add course details with learning outcomes, assessments, syllabus documents, mapped skills, and history.
- Add curriculum version comparison.
- Add draft preview and explicit publication.
- Clone a prior version for a new academic year.
- Add manual learning outcome management with review and evidence.
- Add course-skill mapping CRUD and approval without requiring AI.
- Add mapping explanation: proficiency level, evidence, source, confidence, reviewer, and date.
- Avoid deleting a course that is referenced by a published curriculum or historic analysis.

## 4. Skills catalog

### Current
- Shared categories, skills, and aliases exist.
- A seed catalog is provided.
- Authorized screens can list and create skills.

### Improve
- Add catalog search, filtering, category management, alias management, and duplicate detection.
- Add canonical skill detail with linked courses, outcomes, job requirements, assessments, and framework mappings.
- Restrict global catalog changes.
- Provide a suggestion workflow for organizations to request new skills.
- Add safe merge/redirect of duplicates and usage-impact preview.
- Add external skill framework references only with version and provenance.

## 5. Syllabus documents and AI review

### Current
- PDF upload, private storage, SHA-256 duplicate detection, explicit AI consent, queue dispatch, structured AI output, and suggestion review exist.

### Improve
- Add document detail metadata: course, academic year, revision, file hash, upload actor, consent record, processing state, provider/model, and run history.
- Provide independent review for course summary, learning outcomes, and skill suggestions.
- Preserve evidence text and page number for each proposed outcome and skill.
- Let reviewers match an AI suggestion to an existing canonical skill or propose a new skill.
- Add reject reason and reviewer note.
- Add re-run/version handling without overwriting a previous review.
- Add private authorized preview/download and deletion/retention policy.
- Make queue progress and failure reason useful without exposing provider internals.
- Validate actual PDF content and page references; do not rely only on a client-provided MIME type.

## 6. Labor market and job profiles

### Current
- Occupations, job profiles, requirements, postings, AI extraction, approval, and profile creation/update are present.

### Improve
- Add edit/archive/view flows and profile history.
- Add a review screen for job extraction with evidence and confidence.
- Detect equivalent skills through aliases/canonical mappings.
- Support required/preferred state and explain its effect in scoring.
- Add manual requirement management with the same review/source standards as AI extraction.
- Add import/export templates.
- Record source organization, URL, collection date, license, country, and source version.
- Keep external labor-market facts separate from institution-specific interpretations.
- Add employer accounts only if an employer-facing user journey is a required project feature.

## 7. Student management and portal

### Current
- Institution can create student accounts.
- Student can set a career target, submit self-assessment, add projects, and view assessment/project records.
- Staff can add and verify skill assessments.

### Improve
- Add student profile edit and enrollment status lifecycle.
- Add assessment review notes and rejection/request-changes actions.
- Add a clear rubric for levels 1–5 and evidence examples.
- Add private certificate upload, review, expiry, and download.
- Add project review and reviewer comments.
- Show students which evidence has been accepted and which is still provisional.
- Add progress history and compare successive analyses.
- Keep student evidence private and organization-scoped.

## 8. Analysis

### Current
- Curriculum and student analysis are calculated by `CurriculumGapAnalysisService`.
- Results are stored as analysis runs and per-skill results.
- Historical input snapshots and rule recommendations exist.
- Optional AI recommendation job is dispatched when a provider key exists.

### Improve
- Add a methodology version and preserve exact score inputs.
- Show a per-skill explanation with source evidence.
- Show curriculum coverage and student assessed coverage as different concepts.
- Add compare-runs and curriculum-version comparisons.
- Add robust handling for no courses, no approved mappings, no requirements, no verified assessments, and zero total weight.
- Decide and document how preferred requirements affect the result.
- Add a clear “insufficient evidence” state instead of displaying a misleading score when appropriate.
- Keep all historic results stable after source records change.

## 9. Recommendations

### Current
- Rule-based recommendations are created for gaps of at least 20%.
- AI recommendations can be stored separately and begin pending.

### Improve
- Add recommendation list/detail screens.
- Add status transitions: pending, accepted, in_progress, completed, dismissed.
- Add assignee, due date, notes, and status history.
- Link each recommendation to the relevant skill result and evidence.
- Separate AI draft approval from the act of completing a recommendation.
- Allow institution administrators to report completion rates and overdue actions.
- Do not claim that an AI recommendation is an official course, certification, or intervention unless approved.

## 10. Reporting and exports

### Add
- Curriculum alignment report.
- Student readiness report.
- Skill-by-skill evidence report.
- Curriculum version comparison report.
- Pending review and recommendation action report.
- CSV/XLSX templates for controlled data exchange.
- PDF report with methodology, organization, filters, generated date, and source references.

All reports must enforce the same Policies and tenant scoping as the underlying page.

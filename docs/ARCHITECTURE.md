# System architecture

## Architectural style

CSGA is a Laravel monolith with domain-oriented services. Blade and Tailwind render the UI. Laravel owns authentication, authorization, organization boundaries, persistence, deterministic scoring, queue processing, and AI provider calls.

This architecture keeps development and deployment approachable for a student team while maintaining clear domain boundaries.

## Domains

| Domain | Responsibility |
|---|---|
| Identity and tenancy | Organizations, users, roles, permissions |
| Academic | Departments, programs, curriculum versions, courses, outcomes |
| Skills | Skill taxonomy, categories, aliases, relationships, external mappings |
| Labor market | Occupations, job profiles, employers, postings, weighted requirements |
| Student | Profiles, skill assessments, projects, certificates, career targets |
| AI | Syllabus extraction, job extraction, provider metadata, human review |
| Analysis | Deterministic curriculum and student scoring |
| Recommendations | Rule-based and AI-drafted actions |
| Reporting | Dashboard summaries and historical snapshots |

## Data flow

~~~mermaid
flowchart TD
    A[Course syllabus PDF] --> B[Private storage]
    B --> C[Queued syllabus agent]
    C --> D[Structured outcome and skill suggestions]
    D --> E[Instructor review]
    E --> F[Approved course-skill mappings]
    G[Job description] --> H[Queued job agent]
    H --> I[Skill requirement suggestions]
    I --> J[Advisor review]
    J --> K[Approved job requirements]
    F --> L[Curriculum analysis service]
    K --> L
    M[Verified student evidence] --> N[Student analysis service]
    K --> N
    L --> O[Saved analysis snapshot]
    N --> O
    O --> P[Rule recommendations]
    O --> Q[Optional AI recommendations]
    P --> R[Analysis report]
    Q --> S[Pending review actions]
~~~

## Trust boundaries

1. Uploaded documents and job descriptions are untrusted source content.
2. AI output is provisional until validated and reviewed.
3. Official curriculum scoring consumes approved course mappings only.
4. Student scoring consumes verified assessment records only.
5. Organization-owned records are scoped by the authenticated user's organization.
6. Shared skill and occupation references can be reused across organizations.
7. Files use private storage; they are not served through public direct URLs.

## Tenancy model

An organization owns departments, courses, job profiles, student profiles, documents, and analysis runs. Each user belongs to an organization. Shared skills and external framework reference entities are global catalog data.

Never accept an organization ID from a request as proof of access. Resolve and scope tenant-owned records through the authenticated user.

## Background processing

The following jobs perform provider work outside HTTP requests:

- AnalyzeSyllabusDocument
- AnalyzeJobPosting
- GenerateGapRecommendations

Jobs should record provider/model metadata, support retries, update a visible status, and store safe error details.

## Historical analysis

Analysis runs store a snapshot of important input values and one result record per required skill. Changing a curriculum or job profile later must not silently recalculate a past result.

## Failure handling

- Invalid uploads are rejected before queue dispatch.
- AI failures set the related run and source document/posting to failed.
- Analysis requests require at least one course and one job skill requirement.
- AI recommendation failure does not invalidate a deterministic score.
- Cross-organization resource access returns 404.

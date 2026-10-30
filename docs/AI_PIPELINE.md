# AI pipeline and governance

## Provider settings

Laravel AI SDK agents use the application configuration:

~~~dotenv
OPENAI_API_KEY=
CSGA_AI_PROVIDER=openai
CSGA_AI_MODEL=gpt-4.1-mini
CSGA_AI_TIMEOUT=120
~~~

The API key stays on the server and must never be exposed to browser JavaScript or committed to Git.

## Syllabus analysis

1. An authorized instructor uploads a PDF and explicitly confirms permission to submit its content to the configured provider.
2. The application validates file type and size, stores the file privately, and records SHA-256 for duplicate detection.
3. AnalyzeSyllabusDocument is queued.
4. CourseSyllabusAnalyzer extracts a summary, learning outcomes, and atomic skill suggestions with structured output.
5. Suggestions include proposed levels, confidence, page number, and supporting evidence.
6. The result is recorded in ai_runs and document_skill_suggestions.
7. An authorized reviewer approves or rejects each suggestion.
8. Approved skills become course_skill_mappings and approved learning outcomes become course_outcomes.

AI does not directly write an approved curriculum mapping.

## Job-description analysis

1. An authorized user stores a job posting.
2. AnalyzeJobPosting submits title and description to JobDescriptionAnalyzer.
3. The agent suggests normalized skills, required/preferred status, levels, importance, confidence, and evidence.
4. job_skill_extractions stores the proposals.
5. An advisor approves selected proposals.
6. Approval creates or updates an organization-owned job profile and job_skill_requirements.

The original posting remains source evidence.

## Recommendations

After deterministic scoring, GenerateGapRecommendations may send verified gap data to GapRecommendationAgent. It may draft practical actions but must not invent a skill or claim that an action is complete.

Rule recommendations remain available without an AI key. AI recommendations are saved with source=ai and status=pending.

## Validation

Structured output constrains shape and field ranges. Application code still validates:
- skill levels between 1 and 5;
- confidence between 0 and 1;
- identifiers against the local catalog;
- page numbers and evidence;
- organization ownership;
- posting association before approval.

A valid JSON shape does not prove that the model's interpretation is academically correct.

## Prompt injection

Documents and job descriptions are untrusted input. Agent instructions tell the model to ignore embedded instructions and analyze only academic or job-related content. Agents are not given tools that can modify or approve official records.

## Privacy and retention

- Syllabus upload requires explicit consent for provider processing.
- Documents use private Laravel storage by default.
- Avoid sending student identifiers or sensitive personal data to the provider.
- Use synthetic data in tests and demonstrations.
- Agree a data retention policy with the institution before production.
- Document provider, data region, and provider retention terms before uploading confidential materials.

## Reliability and cost

- Provider requests run in queue workers.
- Duplicate syllabus files are detected by hash within an organization.
- Provider/model/status/output/error metadata is persisted.
- Keep prompts and response schemas bounded.
- Set provider quotas before enabling bulk uploads.

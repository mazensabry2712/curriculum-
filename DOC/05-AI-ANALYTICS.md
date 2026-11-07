# 05 — AI Governance, Scoring, and Evaluation

## Core principle

AI is an assistant for evidence extraction and draft recommendations. The official score must remain deterministic, reproducible, and explainable from reviewed data.

## Current AI flows

### Syllabus flow
1. Instructor uploads a PDF and confirms consent for provider processing.
2. The document is stored on a private disk and hashed.
3. A queued job calls `CourseSyllabusAnalyzer`.
4. Structured output contains summary, learning outcomes, and skill proposals.
5. The run and proposed skills are saved.
6. A reviewer approves/rejects suggestions.
7. Approved mappings are used by curriculum analysis.

### Job-description flow
1. An authorized user stores a job posting.
2. A queued job calls `JobDescriptionAnalyzer`.
3. Structured output contains proposed job skills, levels, importance, required/preferred state, confidence, and evidence.
4. An advisor reviews selected proposals.
5. Approved requirements are added to an organization-owned job profile.

### Recommendation flow
1. Deterministic analysis creates rule-based recommendations.
2. An optional queued AI job receives the analyzed gaps.
3. The agent creates candidate actions linked to supplied skill IDs.
4. AI recommendations remain pending human review.

## Required improvements to AI processing

### Document extraction
- Review summary, each outcome, and each skill as separate suggestion records.
- Preserve evidence and page reference for every suggestion.
- Validate all structured fields again in application code.
- Confirm page numbers against the actual PDF page count where feasible.
- Normalize suggested skills through aliases/canonical matching.
- Do not auto-create a skill solely because the model returned a new name.
- Store prompt version, schema version, provider, model, and source hash for each run.
- Retain previous runs and reviewer decisions when a document is reprocessed.
- Add a reviewer note and rejection reason.
- Make retry behavior idempotent.

### Prompt safety
- Treat uploaded PDFs and job descriptions as untrusted source content.
- Tell the model to analyze content, not follow embedded instructions.
- Do not give extraction agents tools that can approve data or mutate official records.
- Send only the minimum source data needed.
- Do not send student identifiers or personal student records unless a separately approved, documented requirement exists.
- Keep provider keys on the server.
- Redact private information from logs, error monitoring, and AI usage metadata.

### Provider controls
- Validate provider and model configuration at startup or before dispatch.
- Set realistic timeouts, retry/backoff, queue attempts, and job uniqueness/idempotency.
- Distinguish invalid document, provider rate limit, provider outage, malformed structured result, and application persistence errors.
- Expose safe user-facing messages while preserving technical detail in restricted logs.
- Track request IDs and token/cost metadata only when the provider supplies it.
- Add per-organization quotas or manual safeguards before bulk processing.

## Scoring methodology

The proficiency scale is 1–5:

| Level | Meaning |
|---:|---|
| 1 | Awareness |
| 2 | Understanding |
| 3 | Guided application |
| 4 | Independent application |
| 5 | Advanced independent practice |

Every numeric level must have an evidence rubric. The model must not assign a level by counting keywords.

### Skill gap

For a required level (R_i) and current level (C_i):

[
Gap_i = \max(0, (R_i-C_i)/R_i)\times100
]

Coverage above the required level is capped at the requirement.

### Weighted alignment

[
Alignment = \frac{\sum_i W_i\times\min(C_i,R_i)/R_i}{\sum_i W_i}\times100
]

The current implementation uses the weighted contribution of assessed skills for student analysis and reports assessed coverage separately.

### Curriculum coverage

Approved course-skill mappings are weighted by course credit hours and the configured elective factor:

[
C_i = \frac{\sum_j H_j\times E_j\times L_{ij}}{\sum_j H_j\times E_j}
]

Where:
- (H_j): course credit hours.
- (E_j): required-course factor (1) or elective factor (currently 0.65 by default).
- (L_{ij}): approved coverage level for skill (i) in course (j).

No approved mapping means zero curriculum coverage for that skill.

### Student analysis
- Use the latest verified assessment per required skill.
- Do not treat a missing assessment as level zero.
- Report assessed coverage as the fraction of total requirement weight supported by verified evidence.
- Display “not assessed” for missing evidence.
- Do not convert project tags or self-assessment into verified proficiency without an explicit reviewer assessment.

## Methodology decisions still required

Before changing the formula, agree and document:

1. Should preferred requirements contribute to alignment with a reduced weight, or be excluded from the primary score?
2. Are all course-skill mappings equally authoritative, or should outcome/assessment evidence affect coverage?
3. Should repeated coverage of the same skill across multiple courses be capped at the required level before aggregation?
4. Should a curriculum with no approved mappings show 0% alignment or an “insufficient approved evidence” state?
5. What minimum assessed-coverage threshold is needed before publishing a student readiness score?
6. Should an assessment expire or become stale after a defined period?
7. Which rubric defines level 1–5 for each skill family?

Do not change the scoring formula until a benchmark suite is written and the expected outcomes are approved.

## Immutable analysis snapshots

Every analysis should preserve:
- analysis type and subject IDs;
- curriculum version and program identifiers;
- job profile and exact requirement values;
- skill IDs and stable names used at analysis time;
- current and required levels;
- importance weights and required/preferred state;
- mapping/assessment evidence and reviewer references;
- elective/credit-hour weighting inputs;
- methodology version;
- generated timestamp and actor.

The persisted per-skill result records are also part of the historical report. A past report must never recalculate itself from mutable source records.

## Recommendation quality

- Keep rules as the baseline because they are deterministic and available without an AI key.
- Use AI only for richer action wording or optional recommendations.
- Require AI output to reference a supplied skill ID.
- Validate that no unsupported course, certification, or employer fact is introduced.
- Keep source = rules/AI and status = pending/approved/completed distinguishable.
- Store reviewer decision and, where appropriate, the final edited recommendation text.

## Evaluation plan

Create an expert-reviewed synthetic or permission-cleared dataset:
- at least 10 representative syllabi;
- at least 5 job profiles;
- a canonical skill list and alias list;
- human-reviewed learning outcomes and skill labels;
- hand-calculated scoring scenarios including boundary cases.

Measure:
- precision, recall, and F1 for extracted skills;
- outcome extraction quality;
- canonical matching accuracy;
- level agreement with expert reviewers;
- confidence calibration;
- reviewer acceptance and rejection rates;
- duplicate/false-positive rate;
- provider failure rate and average processing time.

Publish evaluation methodology, sample sizes, and limitations. Do not advertise an AI accuracy percentage until the evaluation has actually been run.

## No-key demonstration mode

The project must remain demonstrable without a live AI key:
- Seed synthetic organizations, curricula, skills, profiles, assessments, and job requirements.
- Provide reviewed sample mappings.
- Keep deterministic analysis and reports functional.
- Label sample data clearly.
- Make AI extraction an optional live capability.

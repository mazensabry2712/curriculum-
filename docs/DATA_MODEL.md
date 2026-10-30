# Data model

The schema is grouped around academic structure, skills, labor-market requirements, student evidence, AI review, and analysis history.

## Academic structure

- organizations: institution or workspace boundary.
- departments: academic departments within an organization.
- programs: degree programs within a department.
- curriculum_versions: version identifiers and lifecycle status.
- courses: organization-owned course catalog.
- curriculum_courses: course membership, semester, elective/required status, and order per curriculum version.
- course_outcomes: measurable learning outcomes for a course.
- curriculum_assessments: assessment types and weights.

A course may appear in many curriculum versions. This allows historic comparisons without overwriting previous course structures.

## Skills taxonomy

- skill_categories: skill groups.
- skills: canonical skill definitions and optional framework URI.
- skill_aliases: alternate names and normalized aliases.
- skill_relationships: prerequisites, related skills, or equivalents.
- skill_frameworks: imported reference systems such as ESCO and O*NET.
- external_skill_mappings: local skills mapped to external URIs and codes.
- course_skill_mappings: course coverage level, evidence, confidence, and review state.
- outcome_skill_mappings: learning outcome to skill links.

Levels use an ordinal scale from 1 to 5. Curriculum analysis coverage may be zero when no approved mapping exists.

## Labor market

- occupations: reusable occupation taxonomy.
- job_profiles: target role/seniority profiles, optionally scoped to an organization.
- job_skill_requirements: required level, importance weight, and required/preferred flag.
- employers: employer account details.
- job_market_sources: source provenance.
- job_postings: a specific advertisement or collected description.
- job_skill_extractions: AI-proposed skills and requirements.
- job_ai_runs: job-description AI execution history.

A job posting is source material. It only becomes an official job profile requirement after a reviewer approves extracted skills.

## Student evidence

- student_profiles: organization, program, number, and status.
- student_targets: career target records.
- student_skill_assessments: level, type, confidence, verifier, evidence, and timestamp.
- student_projects: portfolio project records.
- student_project_skills: skills claimed for a project.
- student_certificates: certificate metadata and private file reference.

Self-assessments are provisional. Student analysis uses the latest verified assessment for each required skill.

## AI and review

- documents: private syllabus file metadata, hash, owner, course, and processing status.
- ai_runs: provider/model execution metadata and extraction output.
- document_skill_suggestions: skill proposals with confidence, evidence, page number, and review status.
- job_ai_runs: job-description model execution history.

Only private storage paths and metadata are stored in the database, not public document URLs.

## Analysis

- analysis_runs: analysis type, organization, subject, target job profile, status, scores, and input snapshot.
- analysis_skill_results: one row per required skill with required/current levels, gap, weight, contribution, status, and evidence.
- recommendations: rule-based or AI-drafted actions. AI suggestions begin as pending.

Analysis snapshots preserve the inputs used to calculate historic scores.

## Authorization

Roles, permissions, and polymorphic pivot tables implement Spatie Laravel Permission. Tenant ownership must still be checked independently of role membership.

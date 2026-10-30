# Academic methodology and evaluation

## Proficiency scale

| Level | Meaning | Evidence example |
|---:|---|---|
| 1 | Awareness | Identifies key terms and explains the basic purpose |
| 2 | Understanding | Explains concepts and recognizes suitable use |
| 3 | Guided application | Completes a task with instructions or supervision |
| 4 | Independent application | Completes a practical task independently |
| 5 | Advanced practice | Handles complex tasks, trade-offs, and unusual cases |

These levels are ordinal indicators. They require a rubric and evidence, not keyword frequency alone.

## Job requirements

Each job profile defines:
- required skill level R, from 1 to 5;
- importance weight W, from 1 to 5;
- required or preferred status;
- evidence source.

A job requirement should be reviewed by a career advisor or subject-matter expert. AI extraction and external datasets are candidate evidence, not ground truth.

## Curriculum coverage

Only approved course-skill mappings are used. Required courses are weighted by credit hours. Elective courses are weighted by credit hours multiplied by the configured elective factor (0.65 by default).

For skill i:

\[
C_i = \frac{\sum_j H_j \times E_j \times L_{ij}}{\sum_j H_j \times E_j}
\]

where H is credit hours, E is the required/elective factor, and L is approved course coverage. A skill with no approved mapping has zero curriculum coverage.

## Skill gap

\[
Gap_i = \max(0, (R_i-C_i)/R_i)\times100
\]

Coverage above the required level does not make the gap negative.

## Weighted curriculum alignment

\[
Alignment = \frac{\sum_i W_i \times \min(C_i,R_i)/R_i}{\sum_i W_i}\times100
\]

All requirements are included. Missing curriculum coverage therefore reduces alignment.

## Student analysis

Student analysis uses the latest verified assessment for each job-required skill. Unverified self-assessments and skills without an assessment are not treated as proficiency zero.

Two values are reported:
- alignment score: weighted alignment over skills with verified evidence;
- assessed coverage: total weight of verified skills divided by total weight of all job requirements.

This avoids conflating missing measurements with lack of ability.

## Limitations

- A syllabus describes intended instruction; it does not prove each student mastered a skill.
- Course mapping should cite outcomes, content, or assessments.
- Credit hours are a proxy for curriculum emphasis, not a direct measurement of mastery.
- Student evidence quality depends on rubric design and assessor calibration.
- A job posting represents one employer at one time, not the entire labor market.
- External taxonomies need a recorded version/date and local review.

## Evaluation plan

Create a manually labeled evaluation set with at least 10 syllabi, 5 target job profiles, and a skill reference list reviewed by instructors or domain experts.

Evaluate AI extraction against expert labels using precision, recall, and F1. Evaluate the deterministic scoring engine using hand-calculated benchmark cases and unit tests. Compare AI proposals with expert review and report disagreements.

Do not claim extraction accuracy until the labeled evaluation set has actually been run.

# Team workflow for 6+ contributors

## Workstreams

| Role | Responsibility |
|---|---|
| Team lead / analyst | Requirements, architecture, backlog, integration decisions |
| Academic backend | Departments, programs, curricula, courses, outcomes |
| Skills and labor-market backend | Skill catalog, aliases, occupations, job profiles, requirements |
| AI engineer | Laravel AI agents, document/job extraction, prompt/schema validation |
| Analysis engineer | Scoring services, snapshots, rule recommendations |
| UI engineer | Blade/Livewire screens, responsive UI, accessibility |
| QA / integration | Tests, security checks, fixtures, CI, demo and documentation |

For an eight-person team, split student portfolio/career flows and data import/report work into separate workstreams.

## Branch policy

- main is the stable integration branch.
- Use feature branches such as feature/academic-catalog, feature/ai-syllabus, and fix/analysis-scoring.
- Do not commit directly to main during normal team development.
- Submit pull requests with a clear scope, migration notes, screenshots for UI work, and tests.
- Require a reviewer before merge.
- Coordinate migration creation centrally to prevent conflicting schema changes.
- Keep migrations, route names, JSON schemas, skill levels, tenant rules, and result statuses documented.

## Pull request checklist

- [ ] Change is limited to the assigned domain.
- [ ] Tenant ownership is checked on the server.
- [ ] Validation and authorization are present.
- [ ] AI output remains provisional until reviewed.
- [ ] Migrations have rollback logic.
- [ ] Unit/feature tests are added or updated.
- [ ] PHP tests pass.
- [ ] Frontend build passes for UI changes.
- [ ] No secrets, real student data, or private PDFs are committed.

## Integration cadence

Merge small pull requests frequently. At the end of each sprint, run migrations against an empty database, seed the shared catalog, and test both primary scenarios: curriculum-to-job comparison and student-to-job comparison.

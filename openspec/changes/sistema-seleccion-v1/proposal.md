# Proposal: Sistema de Selección v1

## Intent

Centralized personnel selection management. Interviewers and administrators track applicants, vacancies, interviews, tests, and results. No system exists today — all tracking is manual. Applicants do not interact with the system.

## Scope

### In Scope
- RBAC: Administrador, Entrevistador, custom roles via spatie/laravel-permission
- Vacancy CRUD + test configuration per vacancy (types, max scores, min grade, weighted average thresholds)
- Applicant registration + document uploads + multi-vacancy independent status + admin blocking with alerts
- Interview scheduling (virtual/presencial), status lifecycle, interviewer assignment (self + admin)
- Test CRUD per vacancy: numeric, text, multiple-choice types
- Test results: score recording, system-calculated weighted average, human-determined final apt/no apt
- Candidate comparison reports + PDF/Excel export
- MySQL migration

### Out of Scope
- Applicant self-service portal (no applicant login)
- Zoom/Meet/calendar integration (virtual = link field only)
- Email notifications
- Frontend automated tests (Vitest deferred)
- Real-time or WebSocket features

## Capabilities

### New Capabilities
- `rbac`: spatie/laravel-permission, role seeders, dynamic permission CRUD for custom roles
- `vacancy-management`: CRUD; test config per vacancy (types, max scores, evaluation criteria)
- `applicant-management`: Registration; document uploads; multi-vacancy pivot with independent status; admin blocking with assignment alerts
- `interview-management`: Schedule; status lifecycle; interviewer assignment; observations
- `test-management`: CRUD linked to vacancies; configurable types (numeric, text, multiple-choice)
- `test-results`: Score recording per applicant per test; system calculates min grade + weighted average; human determines final status
- `reports-statistics`: Candidate comparison, score averages, selection tracking, PDF/Excel export

### Modified Capabilities
None — no existing specs.

## Approach

Module-by-module in dependency order (exploration Approach A):
1. Foundation: MySQL config + spatie/laravel-permission + role seeders
2. Vacancy Management → 3. Applicant Management → 4. Test Management → 5. Test Results + Scoring Engine → 6. Interview Management → 7. Reports & Export

TDD enforced via Pest. FormRequest classes from phase 1. All UI uses shadcn-vue components. Each phase delivers a working, testable module.

## Affected Areas

| Area | Impact |
|------|--------|
| `.env`, `config/database.php` | Modified — MySQL |
| `composer.json` | Modified — add spatie/laravel-permission |
| `database/migrations/` | New — 7+ tables |
| `app/Models/` | New — Vacancy, Applicant, Interview, Test, TestResult, VacancyApplicant |
| `app/Http/Controllers/` | New — ~15 controllers across 6 modules |
| `app/Http/Requests/` | New — FormRequest per module |
| `routes/web.php` | Modified — resource routes |
| `resources/js/pages/` | New — ~20 Vue pages |
| `resources/js/layouts/` | Modified — sidebar nav entries |
| `tests/Feature/` | New — Pest tests for all backend logic |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Dynamic test types → UI complexity | High | Isolate scoring engine; v1 limits to 3 test types |
| No frontend test runner | Medium | Install Vitest post-v1; manual QA for v1 UI |
| Multi-vacancy applicant state complexity | Medium | Pivot table with explicit status enum; unit-test transitions |
| RBAC granularity may need iteration | Low | Broad role gates first; refine with spatie's permission CRUD |

## Rollback Plan

Fresh Laravel project with no production data. Rollback: drop MySQL database, remove spatie from composer.json, delete new migrations/models/controllers/pages, revert `.env` to SQLite. Each phase independently reversible via `migrate:rollback`.

## Dependencies

- `spatie/laravel-permission` Composer package
- MySQL database (`sistema_seleccion`) created before first migration
- Report export library (barryvdh/laravel-dompdf or phpoffice/phpspreadsheet — decide in design phase)
- (Optional) `vitest` + `@vue/test-utils` for future frontend testing

## Success Criteria

- [ ] All 27 existing Pest tests pass on MySQL
- [ ] Admin CRUDs vacancies, tests, roles; Entrevistador registers applicants, schedules interviews, records results
- [ ] System calculates weighted average per vacancy; human determines apt/no apt
- [ ] Blocked applicant assignment triggers alert
- [ ] Candidate comparison report exports to PDF and Excel
- [ ] All new backend logic covered by Pest tests

## Proposal Question Round

Assumptions needing user confirmation before specs phase:

1. **Interviewer visibility**: Should interviewers see all applicants or only assigned ones? Default assumption: all applicants, with optional filter toggle.
2. **Apt/no apt scope**: Per vacancy (across all tests via weighted average) or per individual test? Assumption: once per vacancy.
3. **Report export priority**: PDF, Excel, or both for day 1? Assumption: both, with candidate comparison as top-priority report.
4. **File upload constraints**: Max size and allowed formats for CV/certificates? Assumption: 5 MB, PDF/DOCX/JPG.

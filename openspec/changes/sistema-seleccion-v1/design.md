# Design: Sistema de Selección v1

## Technical Approach
Layered Laravel 12 + Inertia 2 + Vue 3, module dependency order (RBAC → Vacancies → Applicants → Tests → TestResults → Interviews → Reports). RBAC gates every route via spatie/laravel-permission. Pure `ScoringService` + `SelectionQueryService` (no Eloquent writes) keep math testable and shared between controllers, UI, and reports. Vue: `useForm` + `AppLayout` + shadcn-vue, per-module pages auto-resolved. Tests stay on SQLite in-memory.

## 1. Architecture Overview
Outer middleware: `auth` + `verified` (Breeze). Inner: `role:Admin` or `permission:...` per route group. Services hold domain logic; controllers orchestrate and are the only DB write path.

## 2. Data Model + 3. Database Schema
Entities: `User` (+ `HasRoles`), `Vacancy`, `Applicant`, `ApplicantDocument`, `Test`, `TestQuestion`, `VacancyTest` (pivot + weight), `VacancyApplicant` (pivot + per-vacancy status + final decision), `TestResult`, `TestAnswer`, `Interview`. spatie adds its 5 tables. All migrations reversible; `softDeletes` on business tables; FKs `restrict` except `applicant_documents.applicant_id` (`cascade`).

| Table | Key columns | Constraints |
|---|---|---|
| `vacancies` | id, position, location, requirements text, status enum(open,closed,cancelled), min_grade decimal(5,2), created_by FK users, ts | idx(status) |
| `applicants` | id, name, phone, email unique, address, is_blocked bool, block_reason text null, blocked_by FK users null, blocked_at ts null, created_by FK users, ts | unique(email) |
| `applicant_documents` | id, applicant_id FK cascade, type enum(cv,certificate), filename, original_name, mime_type, size int, path, ts | idx(applicant_id, type) |
| `tests` | id, name, description text, type enum(numeric,text,multiple_choice), max_score decimal(5,2), evaluation_criteria text null, ts | |
| `test_questions` | id, test_id FK cascade, question_text, options json null, correct_answer_indices json null, points decimal(5,2), order int, ts | idx(test_id, order) |
| `vacancy_test` (pivot) | id, vacancy_id FK, test_id FK, weight decimal(5,2), ts | unique(vacancy_id, test_id); CHECK 0-100 |
| `vacancy_applicant` (pivot) | id, vacancy_id FK, applicant_id FK, status enum(registered,in_interview,evaluated,apt,no_apt), final_decided_by FK users null, final_decided_at ts null, justification text null, ts | unique(vacancy_id, applicant_id); idx(status) |
| `test_results` | id, test_id FK, applicant_id FK, vacancy_id FK, evaluator_id FK users, score decimal(5,2), observations text null, is_manual_override bool, override_justification text null, ts | unique(test_id, applicant_id, vacancy_id) |
| `test_answers` | id, test_result_id FK cascade, test_question_id FK, selected_indices json null, is_correct bool, ts | idx(test_result_id) |
| `interviews` | id, vacancy_id FK, applicant_id FK, interviewer_id FK users, scheduled_at datetime, type enum(virtual,presencial), link url null, location_notes text null, status enum(pending,completed,cancelled), cancellation_reason text null, observations text null, completed_at null, ts | idx(interviewer_id, scheduled_at); idx(vacancy_id, status) |

INT-003/004 assignment = setting `interviews.interviewer_id` + updating `vacancy_applicant.status`. No `interview_assignments` table.

## 4. Route Design
`routes/web.php` includes `routes/modules/{module}.php`. `shallow()` on nested. Group middleware: `['auth','verified','permission:view-{module}']`.

```
/admin/roles[/​{role}/permissions]        RoleController, RolePermissionController
/vacancies[/​{vacancy}/{tests|applicants|results}]  VacancyController + nested shallow
/applicants[/​{applicant}/{documents|history}]      ApplicantController + nested
/tests/{test}/results                     TestResultController
/interviews                               InterviewController
/reports[/​{export|pdf|excel}]             ReportController, ReportExportController
```

## 5. Controllers & FormRequests
`app/Http/Controllers/{Module}/*`. One FormRequest per store/update. ~15 controllers, ~20 FormRequests.

| Controller | Methods | FormRequests |
|---|---|---|
| `VacancyController` | resource + close, cancel, reopen | Store, Update, ChangeStatus |
| `ApplicantController` | resource + block, unblock | Store, Update, Block |
| `ApplicantDocumentController` | store, download, destroy | UploadDocument |
| `TestController` (nested) | resource + attachToVacancy | StoreTest, AttachTest |
| `TestResultController` | create, store, update (override) | RecordScore, OverrideScore |
| `VacancyResultsController` | setFinalStatus, show | SetFinalStatus |
| `InterviewController` | resource + complete, cancel | Store, Complete (requires observations), Cancel |
| `ReportController` / `ReportExportController` | comparison, pipeline, averages, completedInterviews, pdf, excel | ReportFilter |
| `RoleController` | resource | Store, Update |

## 6. Vue Component Trees
`resources/js/pages/{Name}.vue` (auto-resolved). Each page: `<AppLayout :breadcrumbs>` + `<Head title>`. shadcn-vue primitives: `Card`, `Input`, `Button`, `Select`, `Checkbox`, `Dialog`, `DropdownMenu`, `Badge`, `Tabs`.

```
pages/admin/roles/   Index, Create, Edit
pages/vacancies/     Index, Create, Edit, Show          (Show = Tabs [Tests, Applicants, Reports])
pages/vacancies/{id}/tests/  Index, Create              (TestQuestionEditor for multiple_choice)
pages/applicants/    Index, Create, Show                (Show = Tabs [Docs, History, Vacancies])
pages/applicants/{id}/documents/  Index
pages/tests/{id}/results/  Create                       (MultipleChoiceGrader when applicable)
pages/interviews/    Index, Create, Show
pages/reports/       Index                             (Tabs [Comparison, Pipeline, Averages, Interviews])
```

Shared `resources/js/components/{module}/`: `VacancyStatusBadge`, `ApplicantStatusBadge`, `BlockAlertDialog`, `TestQuestionEditor`, `MultipleChoiceGrader`, `WeightedAverageDisplay`, `ReportFilters`.

## 7. Scoring Engine
`App\Services\ScoringService` (pure). `weightedAverage(Vacancy, Applicant): {score, meets_min_grade, breakdown}`:

```
Σ (result.score / test.max_score * 100 * vacancy_test.weight) / Σ weights
```

Guard: `Σ weights == 100` validated at `vacancy_test` attach. DTO is read-only; controllers write `vacancy_applicant.status` only on explicit human action (RES-006). Manual override sets `is_manual_override=true` + `override_justification` required (`required_if`).

## 8. Report Export
PDF: `barryvdh/laravel-dompdf` (Blade). Excel: `maatwebsite/excel` v3.7 (`FromQuery`, .xlsx). Sync generation. Shared `SelectionQueryService` feeds both PDF Blade and Excel export. Filter DTO (`vacancy_id`, `date_from`, `date_to`, `interviewer_id`); Entrevistador scope auto-applied (RPT-007), Admin unrestricted.

## 9. File Upload
`local` disk, `storage/app/private/applicants/{applicant_id}/{type}/{uuid}.{ext}`. `UploadDocumentRequest`: `mimes:pdf,docx,jpg,jpeg,png|max:5120` (5 MB). `Storage::download($path, $original_name)`. No S3 in v1 (config in place).

## 10. Architecture Decisions
| # | Decision | Alternative | Rationale |
|---|---|---|---|
| ADR-01 | Pure `ScoringService`, no Eloquent writes | Model method / observer | Unit-testable; single write path. |
| ADR-02 | `vacancy_applicant` is real row, not `belongsToMany` | Pivot without id | Per-vacancy status + final decision (RES-006, APP-003). |
| ADR-03 | Interview assignment = `interviews.interviewer_id` + pivot update | Separate `interview_assignments` | INT-003/004 map to same fields; extra table redundant. |
| ADR-04 | Reports generated sync | Queued jobs | Datasets small; defer until perf requires. |
| ADR-05 | `barryvdh/laravel-dompdf` + `maatwebsite/excel` | phpspreadsheet direct, Snappy | De-facto Laravel, Blade-friendly. |
| ADR-06 | RBAC at route middleware | Policies only | Module-level scoping (RPT-007, RBAC-003) cleaner as middleware. |
| ADR-07 | Per-module route files | Single `web.php` | Module boundaries; smaller PR diffs. |
| ADR-08 | Tests stay on SQLite in-memory | Migrate tests to MySQL | Faster; no v1 feature needs MySQL. |
| ADR-09 | No Vitest in v1 | Add Vitest now | Proposal defers frontend tests. |
| ADR-10 | One FormRequest per store/update | Inline validation | Sets convention; thin controllers. |

## 11. Testing Strategy
Pest 3.8 with `RefreshDatabase` per test class. Factories in `database/factories/`. Files: `tests/Feature/{Module}/*Test.php`, `tests/Unit/Services/*Test.php`.

| Layer | What to test | Approach |
|---|---|---|
| Unit | `ScoringService` math, weight-sum guard, manual-override rules, status transition rules | Pure input/output, no DB. |
| Feature | CRUD, RBAC denial (Admin vs Entrevistador), 403 cross-role, multi-vacancy independence, block-alert, override justification, weighted-average recompute | `actingAs($admin)` + real factories. |
| Integration | One Pest test per non-trivial spec scenario (e.g. RES-004 weighted-average → real DB, assert DTO + persisted pivot) | Full request cycle. |

## 12. Migration Order
`vacancies` → `applicants` → `applicant_documents` → `tests` → `test_questions` → `vacancy_test` (pivot) → `vacancy_applicant` (pivot) → `test_results` → `test_answers` → `interviews`. Matches module delivery; all `down()` reverse creation.

## Open Questions
- [ ] Confirm `barryvdh/laravel-dompdf` + `maatwebsite/excel` (proposal #3).
- [ ] `interviews.interviewer_id` covers INT-003 self-assign, or prefer historical `applicant_assignments` table.
- [ ] `test_answers` as separate table vs `answers` JSON on `test_results` — separate table chosen for per-answer audit.
- [ ] No event-sourced `vacancy_applicant.status` transitions in v1 (simple enum + validation rule).

# Tasks: Sistema de Selección v1

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~5000-7000 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | 8 chained PRs (Foundation → Vacancies → Applicants → Tests → Results → Interviews → Reports → Polish) |
| Delivery strategy | ask-always |
| Chain strategy | stacked-to-main |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: stacked-to-main
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Foundation: RBAC + MySQL | PR 1 | Chain strategy decision required |
| 2 | Vacancy Management | PR 2 | Depends on PR 1 |
| 3 | Applicant Management | PR 3 | Depends on PR 2 |
| 4 | Test Management | PR 4 | Depends on PR 3 |
| 5 | Test Results + ScoringService | PR 5 | Depends on PR 4 |
| 6 | Interview Management | PR 6 | Depends on PR 3 |
| 7 | Reports & Export | PR 7 | Depends on all above |
| 8 | Integration & Polish | PR 8 | Depends on all above |

## Phase 1: Foundation (RBAC + MySQL)

- [x] 1.1 Switch `.env` to MySQL, update `config/database.php`; install spatie/laravel-permission, publish config + migration
- [x] 1.2 Create `RoleSeeder` (Admin + Entrevistador roles with permissions) and `DatabaseSeeder` call; seed 1 admin user
- [x] 1.3 Create `tests/Feature/RBAC/` tests covering RBAC-001 to RBAC-005 (role existence, Admin full access, Entrevistador 403, custom CRUD, middleware)

## Phase 2: Vacancy Management

- [ ] 2.1 Create `vacancies` migration, `Vacancy` model w/ status enum & relationships, Store/Update/ChangeStatus FormRequests
- [ ] 2.2 Create `VacancyController` (resource + close/cancel/reopen) and `routes/modules/vacancies.php` with RBAC middleware
- [ ] 2.3 Create Vue pages: `vacancies/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` (Tabs: Tests, Applicants, Reports)
- [ ] 2.4 Create `VacancyStatusBadge.vue`; Pest feature tests for VAC-001 to VAC-005

## Phase 3: Applicant Management

- [ ] 3.1 Create `applicants`, `applicant_documents` migrations; `Applicant`, `ApplicantDocument` models w/ relationships; Store/Update/Block/UploadDocument FormRequests
- [ ] 3.2 Create `ApplicantController` (resource + block/unblock), `ApplicantDocumentController`; `routes/modules/applicants.php`
- [ ] 3.3 Create Vue pages: `applicants/Index.vue` (filter), `Create.vue`, `Show.vue` (Tabs: Docs, History, Vacancies); `documents/Index.vue`
- [ ] 3.4 Create `ApplicantStatusBadge.vue`, `BlockAlertDialog.vue`; Pest tests for APP-001 to APP-006

## Phase 4: Test Management

- [ ] 4.1 Create `tests`, `test_questions`, `vacancy_test` migrations; `Test`, `TestQuestion`, `VacancyTest` models; StoreTest/AttachTest FormRequests (weight sum ≤100%)
- [ ] 4.2 Create `TestController` (nested resource under vacancies), attachToVacancy controller; `routes/modules/tests.php`
- [ ] 4.3 Create Vue pages: `vacancies/{id}/tests/Index.vue`, `Create.vue`; `TestQuestionEditor.vue` for multiple_choice
- [ ] 4.4 Pest feature tests for TST-001 to TST-006

## Phase 5: Test Results

- [ ] 5.1 Create `test_results`, `test_answers` migrations; `TestResult`, `TestAnswer` models w/ relationships; RecordScore/OverrideScore/SetFinalStatus FormRequests
- [ ] 5.2 Create `ScoringService` (pure: weightedAverage, meetsMinGrade, weightSumGuard) + `tests/Unit/Services/ScoringServiceTest.php`
- [ ] 5.3 Create `TestResultController`, `VacancyResultsController`; `routes/modules/test-results.php`
- [ ] 5.4 Create Vue pages: `tests/{id}/results/Create.vue`; `MultipleChoiceGrader.vue`, `WeightedAverageDisplay.vue`
- [ ] 5.5 Pest feature tests for RES-001 to RES-006 + ScoringService unit tests

## Phase 6: Interview Management

- [ ] 6.1 Create `interviews` migration; `Interview` model w/ status enum & relationships; Store/Complete/Cancel FormRequests
- [ ] 6.2 Create `InterviewController` (resource + complete/cancel); `routes/modules/interviews.php`
- [ ] 6.3 Create Vue pages: `interviews/Index.vue`, `Create.vue`, `Show.vue`
- [ ] 6.4 Pest feature tests for INT-001 to INT-006

## Phase 7: Reports & Export

- [ ] 7.1 Install `barryvdh/laravel-dompdf` + `maatwebsite/excel`; create `SelectionQueryService` (shared query builder for reports)
- [ ] 7.2 Create `ReportController` (comparison/pipeline/averages/interviews), `ReportExportController` (pdf/excel); `routes/modules/reports.php` w/ Entrevistador scope (RPT-007)
- [ ] 7.3 Create PDF Blade templates and Excel `FromQuery` export classes for 4 report types
- [ ] 7.4 Create `reports/Index.vue` (Tabs: Comparison, Pipeline, Averages, Interviews); `ReportFilters.vue`
- [ ] 7.5 Pest feature tests for RPT-001 to RPT-007

## Phase 8: Integration & Polish

- [ ] 8.1 Add sidebar navigation entries and breadcrumbs for all modules in `AppLayout.vue`
- [ ] 8.2 Run full `php artisan test`; run `npm run build`; fix any failures found

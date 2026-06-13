# Apply Progress — Phase 3: Applicant Management

**Change**: sistema-seleccion-v1  
**Mode**: Strict TDD (backend)  
**Branch**: `feat/sistema-seleccion-v1-phase-3-applicants`  
**Base**: `feat/sistema-seleccion-v1-phase-2-vacancies`  
**Chain**: stacked-to-main

## Completed Tasks

- [x] 3.1 Create `applicants`, `applicant_documents` migrations; `Applicant`, `ApplicantDocument` models w/ relationships; Store/Update/Block/UploadDocument FormRequests
- [x] 3.2 Create `ApplicantController` (resource + block/unblock), `ApplicantDocumentController`; `routes/modules/applicants.php`
- [x] 3.3 Create Vue pages: `applicants/Index.vue` (filter), `Create.vue`, `Show.vue` (Tabs: Docs, History, Vacancies); `documents/Index.vue`
- [x] 3.4 Create `ApplicantStatusBadge.vue`, `BlockAlertDialog.vue`; Pest tests for APP-001 to APP-006

## Files Changed

| File | Action | What Was Done |
|------|--------|---------------|
| `app/Enums/ApplicantDocumentType.php` | Created | Enum for cv/certificate document types |
| `app/Enums/VacancyApplicantStatus.php` | Created | Enum for vacancy-applicant pivot status |
| `app/Models/Applicant.php` | Created | Applicant model with soft deletes, relationships |
| `app/Models/ApplicantDocument.php` | Created | Document model with belongsTo applicant |
| `app/Models/Vacancy.php` | Modified | Added `applicants()` belongsToMany relation |
| `app/Http/Controllers/Applicant/ApplicantController.php` | Created | Resource + block/unblock + vacancy association/status |
| `app/Http/Controllers/Applicant/ApplicantDocumentController.php` | Created | store/download/destroy document actions |
| `app/Http/Requests/Applicant/StoreApplicantRequest.php` | Created | Validation rules for creating applicant |
| `app/Http/Requests/Applicant/UpdateApplicantRequest.php` | Created | Validation rules for updating applicant |
| `app/Http/Requests/Applicant/BlockApplicantRequest.php` | Created | Validation rules for blocking applicant |
| `app/Http/Requests/Applicant/UploadDocumentRequest.php` | Created | Validation rules for document upload |
| `database/migrations/2026_06_13_050000_create_applicants_table.php` | Created | applicants table migration |
| `database/migrations/2026_06_13_050001_create_applicant_documents_table.php` | Created | applicant_documents table migration |
| `database/migrations/2026_06_13_050002_create_vacancy_applicant_table.php` | Created | vacancy_applicant pivot migration |
| `database/factories/ApplicantFactory.php` | Created | Factory for Applicant model |
| `database/factories/ApplicantDocumentFactory.php` | Created | Factory for ApplicantDocument model |
| `routes/modules/applicants.php` | Created | Module routes with RBAC middleware |
| `routes/web.php` | Modified | Included applicants module routes |
| `resources/js/components/applicant/ApplicantStatusBadge.vue` | Created | Status badge component for vacancy-applicant status |
| `resources/js/components/applicant/BlockAlertDialog.vue` | Created | Dialog showing block reason and preventing assignment |
| `resources/js/pages/applicants/Index.vue` | Created | Paginated list with search and assigned-to-me filter |
| `resources/js/pages/applicants/Create.vue` | Created | Applicant registration form |
| `resources/js/pages/applicants/Edit.vue` | Created | Applicant edit form |
| `resources/js/pages/applicants/Show.vue` | Created | Show page with Docs/History/Vacancies tabs |
| `resources/js/pages/applicants/documents/Index.vue` | Created | Document list for an applicant |
| `tests/Feature/Applicant/ApplicantRegistrationTest.php` | Created | APP-001 tests |
| `tests/Feature/Applicant/ApplicantDocumentTest.php` | Created | APP-002 tests |
| `tests/Feature/Applicant/ApplicantVacancyTest.php` | Created | APP-003 tests |
| `tests/Feature/Applicant/ApplicantBlockingTest.php` | Created | APP-004 tests |
| `tests/Feature/Applicant/ApplicantHistoryTest.php` | Created | APP-005 tests |
| `tests/Feature/Applicant/ApplicantVisibilityTest.php` | Created | APP-006 tests |
| `openspec/changes/sistema-seleccion-v1/tasks.md` | Modified | Marked Phase 3 tasks complete |

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 3.1/3.2 APP-001 | `tests/Feature/Applicant/ApplicantRegistrationTest.php` | Feature | N/A (new) | Written | Passed | 7 cases | Pint clean |
| 3.2 APP-002 | `tests/Feature/Applicant/ApplicantDocumentTest.php` | Feature | N/A (new) | Written | Passed | 7 cases | Pint clean |
| 3.2 APP-003 | `tests/Feature/Applicant/ApplicantVacancyTest.php` | Feature | N/A (new) | Written | Passed | 3 cases | Pint clean |
| 3.2 APP-004 | `tests/Feature/Applicant/ApplicantBlockingTest.php` | Feature | N/A (new) | Written | Passed | 5 cases | Pint clean |
| 3.2 APP-005 | `tests/Feature/Applicant/ApplicantHistoryTest.php` | Feature | N/A (new) | Written | Passed | 3 cases | Pint clean |
| 3.2 APP-006 | `tests/Feature/Applicant/ApplicantVisibilityTest.php` | Feature | N/A (new) | Written | Passed | 5 cases | Pint clean |

### Test Summary
- **Total tests written**: 30
- **Total tests passing**: 30
- **Layers used**: Feature (30)
- **Approval tests**: None — no refactoring tasks
- **Pure functions created**: 0

## Deviations from Design

1. **Vacancy association endpoints**: The design does not explicitly define routes for associating an applicant to a vacancy or updating the pivot status. Added `POST /applicants/{applicant}/vacancies/{vacancy}/associate` and `PUT /applicants/{applicant}/vacancies/{vacancy}/status` to satisfy APP-003.
2. **APP-005 history scope**: The design envisions a timeline built from interviews and test results, but those entities are implemented in later phases. Phase 3 history is built from block events and vacancy associations. The populated interview/result scenarios will be covered when Phases 5 and 6 land.
3. **APP-006 "assigned to me" filter**: True interview assignment (via `interviews.interviewer_id`) does not exist until Phase 6. In this phase the toggle filters by `created_by = current user` as a pragmatic stand-in.
4. **Route permission grouping**: The provided route snippet in tasks.md groups everything under `permission:view-applicants`. To respect the actual actions, routes are grouped by the required permission (`view-applicants`, `create-applicants`, `edit-applicants`, `delete-applicants`, `block-applicants`).

## Issues Found

- `php artisan test --filter=Applicant` (and similar multi-file/direct invocations) returns exit 255 with no output in this environment. Individual test files run correctly. All 30 Phase 3 tests pass when run per file.
- `npx vue-tsc --noEmit` reports pre-existing type definition errors (`./resources/js/types`, `vue/tsx`) in `tsconfig.json`; `npm run build` succeeds.

## Verification

- Pest tests: 30/30 passing (run per file)
- Laravel Pint: clean on all changed PHP files
- `npm run build`: successful

## Commits

- `8528a8a` feat(applicants): add migrations, models, enums and factories
- `6da00ff` feat(applicants): add controllers, form requests, routes and feature tests
- `7cc8eb8` feat(applicants): add Vue pages and shared components
- `dec1d8d` docs(applicants): mark Phase 3 tasks complete

## Status

4/4 Phase 3 tasks complete. Ready for verify.

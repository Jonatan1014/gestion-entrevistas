# Apply Progress — Phase 6: Interview Management

**Change**: sistema-seleccion-v1  
**Mode**: Strict TDD (backend Pest)  
**Branch**: `feat/sistema-seleccion-v1-phase-6-interviews`  
**Base**: `feat/sistema-seleccion-v1-phase-5-results`  
**Chain strategy**: stacked-to-main

## Completed Tasks

- [x] 6.1 Create `interviews` migration; `Interview` model w/ status enum & relationships; Store/Complete/Cancel FormRequests
- [x] 6.2 Create `InterviewController` (resource + complete/cancel); `routes/modules/interviews.php`
- [x] 6.3 Create Vue pages: `interviews/Index.vue`, `Create.vue`, `Show.vue`
- [x] 6.4 Pest feature tests for INT-001 to INT-006

## TDD Cycle Evidence

| Task | Test File(s) | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|--------------|-------|------------|-----|-------|-------------|----------|
| 6.1 Domain model | `tests/Feature/Interview/ScheduleInterviewTest.php` | Feature / Unit | N/A (new files) | Written | 20 passed | Multiple cases (virtual, presencial, missing link, enum values, relations) | Pint clean |
| 6.2 Controller & routes | `tests/Feature/Interview/*.php` | Feature | N/A (new files) | Written | 20 passed | INT-001..INT-006 scenarios, self/admin assignment, lifecycle guards | Pint clean |
| 6.3 Vue pages | — | Frontend | N/A | N/A | N/A | N/A | N/A |
| 6.4 Feature tests | `tests/Feature/Interview/*.php` | Feature | N/A (new files) | Written | 20 passed | 6 spec scenarios + edge cases | Pint clean |

### Test Summary

- **Total tests written**: 20
- **Total tests passing**: 20 (88 assertions)
- **Layers used**: Feature (20)
- **Approval tests**: None — no refactoring tasks
- **Pure functions created**: 0 (controller orchestration + model relations)

### Safety Net Note

A full `php artisan test` run failed before any modifications due to pre-existing duplicate global helper functions (`createAdmin`) declared in multiple existing `tests/Feature/Applicant/*Test.php` files. This is unrelated to Phase 6 work and was not fixed because it is outside the assigned scope. Targeted `tests/Feature/Interview/` runs were used for the TDD cycle.

## Files Changed

| File | Action | What Was Done |
|------|--------|---------------|
| `app/Enums/InterviewStatus.php` | Created | Status enum: `pending`, `completed`, `cancelled` |
| `app/Enums/InterviewType.php` | Created | Type enum: `virtual`, `presencial` |
| `app/Models/Interview.php` | Created | Model with casts, `belongsTo` vacancy/applicant/interviewer |
| `app/Models/Applicant.php` | Modified | Added `interviews()` HasMany relation |
| `app/Models/User.php` | Modified | Added `interviews()` HasMany relation |
| `app/Models/Vacancy.php` | Modified | Added `interviews()` HasMany relation |
| `app/Http/Controllers/Interview/InterviewController.php` | Created | Resource + `complete`/`cancel` + pivot status update per ADR-03 |
| `app/Http/Requests/Interview/StoreInterviewRequest.php` | Created | Validation rules for scheduling |
| `app/Http/Requests/Interview/CompleteInterviewRequest.php` | Created | Observations required on completion |
| `app/Http/Requests/Interview/CancelInterviewRequest.php` | Created | Cancellation reason required |
| `database/migrations/2026_06_13_122023_create_interviews_table.php` | Created | Reversible migration with indexes |
| `database/factories/InterviewFactory.php` | Created | Factory with virtual/presencial/pending/completed/cancelled states |
| `database/seeders/RoleSeeder.php` | Modified | Added `manage-interviews` and `delete-interviews` permissions |
| `routes/modules/interviews.php` | Created | Module route file with resource + lifecycle routes |
| `routes/web.php` | Modified | Included interviews module routes |
| `resources/js/pages/interviews/Index.vue` | Created | List with filters and complete/cancel dialogs |
| `resources/js/pages/interviews/Create.vue` | Created | Scheduling form with type-conditional fields |
| `resources/js/pages/interviews/Show.vue` | Created | Interview detail with lifecycle actions |
| `resources/js/components/interview/InterviewStatusBadge.vue` | Created | Status badge component |
| `resources/js/components/interview/InterviewTypeBadge.vue` | Created | Type badge component |
| `tests/Feature/Interview/*.php` | Created | Feature tests for INT-001 to INT-006 |

## Deviations from Design

- Added `delete-interviews` permission (not explicitly in the task list) because the resource route includes `destroy` and it needs RBAC protection. Admin gets it via `syncPermissions(Permission::all())`; it is not assigned to Entrevistador.
- The index filter for applicants supports both `applicant_id` (used by tests) and `applicant_name` search (used by the UI), which is a small superset of the spec.
- `interviewer_id` filtering is allowed for all users; Entrevistador users are always scoped to their own interviews first, so they cannot bypass the role scope.

## Issues Found

1. **Pre-existing full-suite failure**: existing `tests/Feature/Applicant/*Test.php` files declare duplicate global `createAdmin()` / `createEntrevistador()` helper functions, causing a fatal redeclaration error when the entire Feature suite runs. Targeted `tests/Feature/Interview/` runs are clean.

## Commits

| Hash | Message |
|------|---------|
| `58a800b` | `feat(interviews): add interview scheduling backend, lifecycle routes and feature tests` |
| `a50ec66` | `feat(interviews): add interview Vue pages and status/type badges` |

## Verification Commands Run

```bash
./vendor/bin/pest tests/Feature/Interview/
# 20 passed (88 assertions)

./vendor/bin/pint --test <changed-php-files>
# PASS — 22 files
```

## Status

4/4 Phase 6 tasks complete. Ready for verify.

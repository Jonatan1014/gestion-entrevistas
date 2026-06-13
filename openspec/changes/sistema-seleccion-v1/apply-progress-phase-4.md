# Apply Progress — Phase 4: Test Management

**Change**: sistema-seleccion-v1  
**Mode**: Strict TDD (backend)  
**Branch**: `feat/sistema-seleccion-v1-phase-4-tests` (stacked from `feat/sistema-seleccion-v1-phase-3-applicants`)  
**Date**: 2026-06-13

## Summary

Implemented all four Phase 4 tasks: test management migrations/models/requests, controllers and routes, Vue pages, and Pest feature tests for TST-001 to TST-006. All Phase 4 tests pass and changed PHP files pass Laravel Pint.

## Completed Tasks

- [x] 4.1 Create `tests`, `test_questions`, `vacancy_test` migrations; `Test`, `TestQuestion`, `VacancyTest` models; StoreTest/AttachTest FormRequests (weight sum ≤100%)
- [x] 4.2 Create `TestController` (nested resource under vacancies), attachToVacancy controller; `routes/modules/tests.php`
- [x] 4.3 Create Vue pages: `tests/Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`; `vacancies/{id}/tests/Index.vue`; `TestQuestionEditor.vue` for multiple_choice
- [x] 4.4 Pest feature tests for TST-001 to TST-006

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 4.1 | `tests/Feature/Test/TestNumericTypeTest.php` | Feature | N/A (new) | Written | Passed | 3 cases | Pint clean |
| 4.1 | `tests/Feature/Test/TestTextTypeTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Pint clean |
| 4.2 | `tests/Feature/Test/TestCrudTest.php` | Feature | N/A (new) | Written | Passed | 7 cases | Pint clean |
| 4.2 | `tests/Feature/Test/TestAssociationTest.php` | Feature | N/A (new) | Written | Passed | 5 cases | Pint clean |
| 4.3 | N/A (Vue) | Frontend | N/A | N/A | N/A | N/A | No test runner available |
| 4.4 | `tests/Feature/Test/TestMultipleChoiceTest.php` | Feature | N/A (new) | Written | Passed | 3 cases | Pint clean |
| 4.4 | `tests/Feature/Test/TestReuseTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Pint clean |

### Test Summary

- **Total tests written**: 22
- **Total tests passing**: 22
- **Layers used**: Feature (22)
- **Approval tests**: None — no refactoring tasks
- **Pure functions created**: `Test::calculateMultipleChoiceScore`, `Test::applyManualOverride`

## Files Changed

| File | Action | What Was Done |
|------|--------|---------------|
| `app/Enums/TestType.php` | Created | Enum for numeric/text/multiple_choice test types |
| `app/Models/Test.php` | Created | Test model with questions relation, soft deletes, scoring helpers |
| `app/Models/TestQuestion.php` | Created | Question model with array casts for options/correct answers |
| `app/Models/VacancyTest.php` | Created | Explicit pivot model for `vacancy_test` |
| `app/Models/Vacancy.php` | Modified | Added `tests()` belongsToMany relation with pivot weight |
| `app/Http/Requests/Test/StoreTestRequest.php` | Created | Validation rules for test creation |
| `app/Http/Requests/Test/UpdateTestRequest.php` | Created | Validation rules for test updates |
| `app/Http/Requests/Test/AttachTestRequest.php` | Created | Validation + custom weight sum ≤100% guard |
| `database/migrations/2026_06_13_050003_create_tests_table.php` | Created | Tests table migration |
| `database/migrations/2026_06_13_050004_create_test_questions_table.php` | Created | Test questions table migration with index |
| `database/migrations/2026_06_13_050005_create_vacancy_test_table.php` | Created | Vacancy-test pivot with unique constraint |
| `database/factories/TestFactory.php` | Created | Factory for `Test` with type states |
| `database/factories/TestQuestionFactory.php` | Created | Factory for `TestQuestion` |
| `database/seeders/RoleSeeder.php` | Modified | Added `manage-tests` permission for route middleware |
| `app/Http/Controllers/Test/TestController.php` | Created | Resource controller for standalone test CRUD |
| `app/Http/Controllers/Test/VacancyTestController.php` | Created | Nested controller for vacancy-test association |
| `routes/modules/tests.php` | Created | Test module routes with RBAC middleware |
| `routes/web.php` | Modified | Included `routes/modules/tests.php` |
| `resources/js/pages/tests/Index.vue` | Created | Test listing page |
| `resources/js/pages/tests/Create.vue` | Created | Test creation form with type selector |
| `resources/js/pages/tests/Edit.vue` | Created | Test edit form pre-filled with questions |
| `resources/js/pages/tests/Show.vue` | Created | Test detail page with questions |
| `resources/js/pages/vacancies/tests/Index.vue` | Created | Vacancy test association and weight management |
| `resources/js/components/test/TestQuestionEditor.vue` | Created | Multiple-choice question editor component |
| `resources/js/pages/vacancies/Show.vue` | Modified | Tests tab now links to vacancy tests management |
| `tests/Feature/Test/*.php` | Created | Pest feature tests for TST-001 to TST-006 |
| `openspec/changes/sistema-seleccion-v1/tasks.md` | Modified | Marked Phase 4 tasks complete |

## Commits

1. `34834d9` — `test(tests): add Phase 4 Pest feature tests (TST-001 to TST-006)`
2. `af92487` — `feat(tests): add test management migrations, models and form requests`
3. `44d5f14` — `feat(tests): add Test and VacancyTest controllers with routes and permissions`
4. `15c7d27` — `feat(tests): add Vue pages and TestQuestionEditor for test management`

## Deviations from Design

- **Weight CHECK constraint**: The design proposed a database CHECK constraint on `vacancy_test.weight`. Laravel's `Blueprint::check()` is not available in this environment, so enforcement is done via FormRequest validation (`min:0`, `max:100`) and a custom validator that ensures the vacancy's total weight does not exceed 100%.
- **Scoring helpers on the Test model**: TST-004 requires auto-calculation and manual override behavior. Since Phase 5 (Test Results / `ScoringService`) has not been implemented yet, temporary pure helpers were added to the `Test` model: `calculateMultipleChoiceScore()` and `applyManualOverride()`. These will be refactored into the pure `ScoringService` in Phase 5 per ADR-01.
- **Vacancy tests page path**: The design references `pages/vacancies/{id}/tests/Index.vue` as a placeholder. Inertia resolves page names, so the actual file is `resources/js/pages/vacancies/tests/Index.vue` and the controller renders `vacancies/tests/Index`.

## Issues Found

None.

## Workload / PR Boundary

- **Mode**: stacked-to-main
- **Current work unit**: Phase 4 — Test Management
- **Boundary**: Branch `feat/sistema-seleccion-v1-phase-4-tests` is stacked on `feat/sistema-seleccion-v1-phase-3-applicants`. This PR contains only the test management slice.
- **Estimated review budget impact**: High-volume slice as forecasted; kept autonomous with tests, domain, controllers, and UI.

## Status

4/4 Phase 4 tasks complete. Ready for verify.

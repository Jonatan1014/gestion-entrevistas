# Apply Progress — Phase 5: Test Results

## Change
sistema-seleccion-v1

## Mode
Strict TDD

## Completed Tasks
- [x] 5.1 Create `test_results`, `test_answers` migrations; `TestResult`, `TestAnswer` models w/ relationships; RecordScore/OverrideScore/SetFinalStatus FormRequests
- [x] 5.2 Create `ScoringService` (pure: weightedAverage, meetsMinGrade, weightSumGuard) + `tests/Unit/Services/ScoringServiceTest.php`
- [x] 5.3 Create `TestResultController`, `VacancyResultsController`; `routes/modules/test-results.php`
- [x] 5.4 Create Vue pages: `tests/{id}/results/Create.vue`; `MultipleChoiceGrader.vue`, `WeightedAverageDisplay.vue`
- [x] 5.5 Pest feature tests for RES-001 to RES-006 + ScoringService unit tests

## Files Changed
| File | Action | What Was Done |
|------|--------|---------------|
| `app/Services/ScoringService.php` | Created | Pure scoring service: weightedAverage, meetsMinGrade, weightSumGuard, calculateMultipleChoiceScore, applyManualOverride |
| `app/Models/TestResult.php` | Created | Model with relations to Test, Applicant, Vacancy, User (evaluator), TestAnswer |
| `app/Models/TestAnswer.php` | Created | Model with casts for selected_indices, relations to TestResult/TestQuestion |
| `app/Models/Test.php` | Modified | Removed temporary helpers; added `results()` relation |
| `app/Models/Applicant.php` | Modified | Added `testResults()` HasMany relation |
| `app/Models/Vacancy.php` | Modified | Added `testResults()` HasMany relation |
| `app/Http/Controllers/TestResult/TestResultController.php` | Created | create, store, show, update for per-test results |
| `app/Http/Controllers/TestResult/VacancyResultsController.php` | Created | index (weighted averages) and setFinalStatus |
| `app/Http/Requests/TestResult/RecordScoreRequest.php` | Created | Validation with dynamic max_score and multiple_choice answers rule |
| `app/Http/Requests/TestResult/OverrideScoreRequest.php` | Created | Override validation with required justification |
| `app/Http/Requests/TestResult/SetFinalStatusRequest.php` | Created | Status apt/no_apt validation |
| `database/migrations/2026_06_13_122021_create_test_results_table.php` | Created | test_results table with unique constraint |
| `database/migrations/2026_06_13_122022_create_test_answers_table.php` | Created | test_answers table with FK cascade and index |
| `database/factories/TestResultFactory.php` | Created | Factory for TestResult with manualOverride state |
| `database/factories/TestAnswerFactory.php` | Created | Factory for TestAnswer with correct/incorrect states |
| `database/seeders/RoleSeeder.php` | Modified | Added record-results, view-results, override-results, set-final-status; assigned relevant ones to Entrevistador |
| `routes/modules/test-results.php` | Created | Test result and vacancy result routes with RBAC middleware |
| `routes/web.php` | Modified | Included test-results route module |
| `resources/js/pages/tests/results/Create.vue` | Created | Form to record a test result |
| `resources/js/pages/tests/results/Show.vue` | Created | Display a recorded result with answers |
| `resources/js/pages/vacancies/results/Index.vue` | Created | Table of applicants with scores, weighted averages, final status dialog |
| `resources/js/pages/vacancies/Show.vue` | Modified | Results tab links to vacancy results page |
| `resources/js/components/test-result/MultipleChoiceGrader.vue` | Created | Renders questions with radio/checkbox, shows correct/incorrect |
| `resources/js/components/test-result/WeightedAverageDisplay.vue` | Created | Displays weighted average with breakdown and color-coded threshold |
| `tests/Unit/Services/ScoringServiceTest.php` | Created | Pure unit tests for all ScoringService methods |
| `tests/Feature/TestResult/RecordScoreTest.php` | Created | RES-001 feature tests |
| `tests/Feature/TestResult/TextTestObservationsTest.php` | Created | RES-002 feature tests |
| `tests/Feature/TestResult/MultipleChoiceAutoCalcTest.php` | Created | RES-003 feature tests |
| `tests/Feature/TestResult/WeightedAverageTest.php` | Created | RES-004 feature tests |
| `tests/Feature/TestResult/MinGradeComparisonTest.php` | Created | RES-005 feature tests |
| `tests/Feature/TestResult/FinalStatusTest.php` | Created | RES-006 feature tests |
| `tests/Feature/Test/TestMultipleChoiceTest.php` | Modified | Refactored to use ScoringService instead of Test model helpers |
| `tests/Feature/Rbac/RoleSeederTest.php` | Modified | Updated expected Entrevistador permissions |

## TDD Cycle Evidence
| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 5.2 | `tests/Unit/Services/ScoringServiceTest.php` | Unit | N/A (new) | Written | Passed | 12 cases | Clean |
| 5.5 | `tests/Feature/TestResult/RecordScoreTest.php` | Feature | N/A (new) | Written | Passed | 4 cases | Clean |
| 5.5 | `tests/Feature/TestResult/TextTestObservationsTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Clean |
| 5.5 | `tests/Feature/TestResult/MultipleChoiceAutoCalcTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Clean |
| 5.5 | `tests/Feature/TestResult/WeightedAverageTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Clean |
| 5.5 | `tests/Feature/TestResult/MinGradeComparisonTest.php` | Feature | N/A (new) | Written | Passed | 2 cases | Clean |
| 5.5 | `tests/Feature/TestResult/FinalStatusTest.php` | Feature | N/A (new) | Written | Passed | 3 cases | Clean |

## Test Summary
- **Total tests written**: 27 (12 unit + 15 feature)
- **Total tests passing**: 27
- **Layers used**: Unit (12), Feature (15)
- **Approval tests**: Refactored `TestMultipleChoiceTest` to use `ScoringService`; existing behavior preserved
- **Pure functions created**: 5 (`weightedAverage`, `meetsMinGrade`, `weightSumGuard`, `calculateMultipleChoiceScore`, `applyManualOverride`)

## Deviations from Design
- The design documented `weightedAverage(Vacancy, Applicant)` as the service signature, but the task spec and tests called for `weightedAverage(array $results, array $weights)`. Implemented the array-based pure version and built the vacancy/applicant arrays in the controller, keeping the service fully testable and reusable.
- The route middleware permissions use the names specified in tasks.md (`record-results`, `view-results`, `override-results`, `set-final-status`) rather than the pre-existing `{action}-test-results` convention. The old permission names were retained in the seeder for backward compatibility, and the new names were added and assigned.

## Issues Found
- `php artisan test` returned no output when run against directories on this environment, so `./vendor/bin/pest` was used for directory runs. Individual file runs with `php artisan test` worked correctly.
- Inertia serializes `84.0` as `84` in JSON, requiring a tolerance-based assertion in `WeightedAverageTest`.
- The `test_answers` migration initially received the same timestamp as `test_results`; it was renamed to `2026_06_13_122022_create_test_answers_table.php` so it runs after `test_results` and can reference its foreign key.

## Workload / PR Boundary
- Mode: stacked-to-main
- Current work unit: Phase 5 — Test Results + ScoringService
- Boundary: starts from `feat/sistema-seleccion-v1-phase-4-tests`, ends with Phase 5 implementation complete
- Estimated review budget impact: This phase is self-contained; approximately 1,300 changed lines across backend/frontend/tests.

## Commit Hashes
- `fba000f` feat(results): add pure ScoringService, refactor Test helpers, add unit tests
- `166e9e4` feat(results): add TestResult and VacancyResults controllers, routes and RBAC permissions
- `dfbb07e` feat(results): add test_results/answers schema, models, form requests, Vue pages and feature tests
- `fdd87c5` chore(git): ignore .DS_Store files

## Status
5/5 tasks complete. Ready for verify.

## Commands Run
```bash
php artisan make:migration create_test_results_table --create=test_results
php artisan make:migration create_test_answers_table --create=test_answers
./vendor/bin/pest tests/Feature/TestResult/RecordScoreTest.php
./vendor/bin/pest tests/Feature/TestResult/TextTestObservationsTest.php
./vendor/bin/pest tests/Feature/TestResult/MultipleChoiceAutoCalcTest.php
./vendor/bin/pest tests/Feature/TestResult/WeightedAverageTest.php
./vendor/bin/pest tests/Feature/TestResult/MinGradeComparisonTest.php
./vendor/bin/pest tests/Feature/TestResult/FinalStatusTest.php
./vendor/bin/pest tests/Unit/Services/ScoringServiceTest.php
./vendor/bin/pest tests/Feature/Rbac/RoleSeederTest.php
./vendor/bin/pest tests/Feature/Test/TestMultipleChoiceTest.php
./vendor/bin/pint --test <changed-php-files>
npm run build
```

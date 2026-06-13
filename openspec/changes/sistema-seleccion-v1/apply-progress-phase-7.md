# Apply Progress — Phase 7: Reports & Export

**Change**: sistema-seleccion-v1  
**Mode**: Strict TDD  
**Branch**: `feat/sistema-seleccion-v1-phase-7-reports`  
**Base**: `feat/sistema-seleccion-v1-phase-6-interviews`  
**Strategy**: Stacked-to-main

## Completed Tasks

- [x] 7.1 Install `barryvdh/laravel-dompdf` + `maatwebsite/excel`; create `SelectionQueryService` (shared query builder for reports)
- [x] 7.2 Create `ReportController` (comparison/pipeline/averages/interviews), `ReportExportController` (pdf/excel); `routes/modules/reports.php` w/ Entrevistador scope (RPT-007)
- [x] 7.3 Create PDF Blade templates and Excel `FromQuery` export classes for 4 report types
- [x] 7.4 Create `reports/Index.vue` (Tabs: Comparison, Pipeline, Averages, Interviews); `ReportFilters.vue`
- [x] 7.5 Pest feature tests for RPT-001 to RPT-007

## TDD Cycle Evidence

| Task | Test File | Layer | Safety Net | RED | GREEN | TRIANGULATE | REFACTOR |
|------|-----------|-------|------------|-----|-------|-------------|----------|
| 7.1 | `tests/Feature/Report/SelectionQueryServiceTest.php` | Integration | N/A (new) | Written | 9 passed | 2 cases per method | Pint clean |
| 7.2 | `tests/Feature/Report/{Comparison,Interviews,Averages,Pipeline,EntrevistadorScope}ReportTest.php` | Feature | N/A (new) | Written | 11 passed | Multi-scenario per report | Pint clean |
| 7.3 | `tests/Feature/Report/ExportTest.php` | Feature | N/A (new) | Written | 8 passed | PDF + Excel for all 4 reports | Pint clean |
| 7.4 | N/A (no frontend test runner) | N/A | N/A | N/A | N/A | N/A | N/A |

## Test Summary

- **Total report tests written**: 28
- **Total report tests passing**: 28
- **Layers used**: Integration (9), Feature (19)
- **Command**: `./vendor/bin/pest tests/Feature/Report` → 28 passed (261 assertions)

## Commits

| Hash | Message |
|------|---------|
| a5a3d64 | feat(reports): add SelectionQueryService for shared report queries |
| 550c6ba | feat(reports): add ReportController, routes, and report feature tests |
| 09c9caa | feat(reports): add PDF and Excel exports for all report types |
| a2cdacc | fix(phase-6): repair interview pagination link and RBAC test expectation |

## Files Changed

| File | Action | Notes |
|------|--------|-------|
| `app/Services/SelectionQueryService.php` | Created | Shared query builder for all reports |
| `app/Http/Controllers/Report/ReportController.php` | Created | Inertia-rendered report tabs |
| `app/Http/Controllers/Report/ReportExportController.php` | Created | PDF/Excel endpoints |
| `app/Exports/{Comparison,Pipeline,Averages,Interviews}Export.php` | Created | `FromQuery` + `WithHeadings` + `WithMapping` |
| `resources/views/reports/{comparison,pipeline,averages,interviews}.blade.php` | Created | Inline-style PDF templates |
| `resources/js/pages/reports/Index.vue` | Created | Tabs + tables + export buttons |
| `resources/js/components/report/ReportFilters.vue` | Created | Vacancy, date range, interviewer filters |
| `routes/modules/reports.php` | Created | Report routes with `view-reports` middleware |
| `routes/web.php` | Modified | Include reports module |
| `tests/Feature/Report/*.php` | Created | Service + RPT-001 to RPT-007 tests |
| `composer.json` / `composer.lock` | Modified | Added dompdf + maatwebsite/excel |
| `resources/js/pages/interviews/Index.vue` | Fixed | Unterminated string in pagination link |
| `tests/Feature/Rbac/RoleSeederTest.php` | Fixed | Added `manage-interviews` to expected Entrevistador permissions |

## Deviations from Design

- `ReportController` paginates comparison/pipeline/averages with 100 items (design does not specify pagination; chosen because datasets are small but UI still benefits from a paginator shape consistent with interviews).
- `ReportExportController` applies Entrevistador scope by injecting `interviewer_id` into filters, reusing the existing `whereExists` filter logic rather than calling `scopeByInterviewer` separately. Result is equivalent.

## Issues Found

1. **Pre-existing fatal error in full test suite**: running the entire Pest suite fails with `Cannot redeclare createAdmin()`. Multiple existing feature test files (`ApplicantBlockingTest.php`, `ApplicantDocumentTest.php`, etc.) declare global helper functions with identical names and no `function_exists` guard. `tests/Feature/Report/Helpers.php` uses prefixed names (`reportCreateAdmin`, etc.) to avoid this.
2. **Pre-existing `vue-tsc` errors**: `TS2688: Cannot find type definition file for './resources/js/types'` and `vue/tsx`. These are configuration-level and unrelated to Phase 7.
3. **Pre-existing build error in `resources/js/pages/interviews/Index.vue`**: unterminated string in `:href="link.url ?? '#'"`. Fixed in commit `a2cdacc`.
4. **Pre-existing RBAC test failure**: `RoleSeederTest` expected Entrevistador permissions did not include `manage-interviews` that `RoleSeeder` actually assigns. Fixed in commit `a2cdacc`.

## Verification

- `./vendor/bin/pest tests/Feature/Report` → 28 passed
- `./vendor/bin/pint --test <changed php files>` → pass
- `npm run build` → success
- `php artisan test` (full suite) → cannot complete due to pre-existing helper-function redeclaration fatal error

## Status

5/5 Phase 7 tasks complete. Ready for verify phase.

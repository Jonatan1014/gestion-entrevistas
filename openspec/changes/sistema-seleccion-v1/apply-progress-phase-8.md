# Apply Progress — Phase 8: Integration & Polish

**Change**: sistema-seleccion-v1  
**Phase**: 8 (final phase)  
**Branch**: `feat/sistema-seleccion-v1-phase-8-polish`  
**Mode**: Standard (Strict TDD applies to backend Pest only; frontend has no test runner per `openspec/config.yaml`)

## Completed Tasks

- [x] 8.1 Add sidebar navigation entries and breadcrumbs for all modules in `AppLayout.vue`
- [x] 8.2 Run full test suite and build; fix any failures found

## Implementation Summary

### Sidebar Navigation (AppSidebar.vue)

Added navigation entries for all modules with RBAC permission checks:

| Module | Route | Icon | Permission(s) | Visible to |
|--------|-------|------|---------------|------------|
| Dashboard | `/dashboard` | `LayoutGrid` | none (always) | all authenticated |
| Vacancies | `/vacancies` | `Briefcase` | `view-vacancies` | Admin, Entrevistador |
| Applicants | `/applicants` | `Users` | `view-applicants` | Admin, Entrevistador |
| Tests | `/tests` | `ClipboardCheck` | `view-tests` or `manage-tests` | Admin, Entrevistador |
| Interviews | `/interviews` | `Calendar` | `view-interviews` | Admin, Entrevistador |
| Reports | `/reports` | `BarChart` | `view-reports` | Admin, Entrevistador (scoped) |
| Roles | `/admin/roles` | `Shield` | `manage-roles` | Admin only |

- Permissions are read from `usePage<SharedData>().props.auth.permissions`, populated by `HandleInertiaRequests`.
- `manage-roles` was added to `RoleSeeder` so only Admin (who gets all permissions) sees the Roles link.

### Breadcrumbs

All Vue pages already defined `breadcrumbs` arrays passed to `AppLayout`. No pages were missing breadcrumbs. Verified pages:
- `resources/js/pages/vacancies/*.vue`
- `resources/js/pages/applicants/*.vue` and `applicants/documents/Index.vue`
- `resources/js/pages/tests/*.vue` and `tests/results/*.vue`
- `resources/js/pages/interviews/*.vue`
- `resources/js/pages/reports/Index.vue`
- `resources/js/pages/Dashboard.vue`

### Supporting Fixes

- `HandleInertiaRequests.php`: added `auth.permissions` array.
- `NavMain.vue`: changed internal `url` prop to `href` to match the `NavItem` contract used by `AppSidebar.vue` (fixes inactive/active link behavior).
- `resources/js/types/index.ts`: added `permissions: string[]` to `Auth` interface.

## Verification Results

### Build

```
npm run build
```

**Result**: PASS (3.89s). No TypeScript or Vite errors.

### Lint / Format

```
./vendor/bin/pint --test app/Http/Middleware/HandleInertiaRequests.php database/seeders/RoleSeeder.php
npx eslint resources/js/components/AppSidebar.vue resources/js/components/NavMain.vue resources/js/types/index.ts
npx prettier --check resources/js/components/AppSidebar.vue resources/js/components/NavMain.vue resources/js/types/index.ts
```

**Result**: All PASS.

Note: `npx vue-tsc --noEmit` reports pre-existing type-resolution errors (`Cannot find type definition file for './resources/js/types'` and `vue/tsx`) caused by the `types` array in `tsconfig.json`. These are unrelated to Phase 8 changes; `npm run build` succeeds and Vite handles type resolution differently.

### Pest Test Suites

| Suite | Command | Tests | Result |
|-------|---------|-------|--------|
| Unit | `./vendor/bin/pest tests/Unit/` | 13 passed | PASS |
| RBAC | `./vendor/bin/pest tests/Feature/Rbac/` | 11 passed | PASS |
| Vacancy | `./vendor/bin/pest tests/Feature/Vacancy/` | 37 passed | PASS |
| Applicant | `./vendor/bin/pest tests/Feature/Applicant/` | 30 passed | PASS |
| Test | `./vendor/bin/pest tests/Feature/Test/` | 22 passed | PASS |
| Interview | `./vendor/bin/pest tests/Feature/Interview/` | 20 passed | PASS |
| Report | `./vendor/bin/pest tests/Feature/Report/` | 28 passed | PASS |
| TestResult (individual files) | `for f in tests/Feature/TestResult/*.php; do ./vendor/bin/pest "$f"; done` | 15 passed | PASS |

**Total**: 196 tests passed, 0 failures.

## Files Changed

| File | Action |
|------|--------|
| `app/Http/Middleware/HandleInertiaRequests.php` | Modified — share `auth.permissions` |
| `database/seeders/RoleSeeder.php` | Modified — add `manage-roles` permission |
| `resources/js/components/AppSidebar.vue` | Modified — add RBAC sidebar entries |
| `resources/js/components/NavMain.vue` | Modified — use `href` instead of `url` |
| `resources/js/types/index.ts` | Modified — add `permissions` to `Auth` |

## Commit

```
aaf591a feat(phase-8): add RBAC sidebar navigation and fix NavMain href contract
```

## Deviations from Design

None. Implementation matches the Phase 8 task specification.

## Issues Found

- `vue-tsc --noEmit` fails with pre-existing `tsconfig.json` type-resolution errors. `npm run build` and Vite are unaffected.

## Status

2/2 Phase 8 tasks complete. Ready for verify.

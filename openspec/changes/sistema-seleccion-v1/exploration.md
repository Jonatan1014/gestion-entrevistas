# Exploration: sistema-seleccion-v1

## Current State

The project is a **Laravel 12.62.0 Breeze starter kit** with the **Inertia + Vue 3 + TypeScript** stack. It has session-based authentication (login, register, password reset, email verification) and a basic dashboard. The database is currently SQLite with 3 migration files creating 8 tables (`users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`). No domain logic beyond auth and settings exists.

## Affected Areas

| Area | File(s) | Why |
|------|---------|-----|
| Database config | `.env`, `config/database.php` | Switch from SQLite to MySQL |
| Auth / RBAC | `composer.json`, `app/Models/User.php`, new migrations | Add spatie/laravel-permission for role-based access |
| Migrations | `database/migrations/` | New tables for vacancies, applicants, interviews, tests, results |
| Models | `app/Models/` | New Eloquent models for all domain entities |
| Controllers | `app/Http/Controllers/` | CRUD and business logic for all 6 modules |
| Routes | `routes/web.php` (new route files) | Resource routes for each module |
| Vue Pages | `resources/js/pages/` | Inertia pages for each CRUD interface |
| Sidebar/Nav | `resources/js/layouts/app/` | Navigation entries for new modules |
| Tests | `tests/Feature/` | Pest feature tests following existing patterns |
| Composer | `composer.json` | Update project name, add spatie/laravel-permission |

## Architecture Analysis

### Auth System
- **Laravel Breeze** (Inertia + Vue stack)
- 8 auth controllers in `app/Http/Controllers/Auth/`
- Session-based, no API tokens, no Sanctum
- Middleware: `auth`, `guest`, `verified`, `signed`, `throttle`

### Permission Model (Current: NONE)
- No `spatie/laravel-permission` or any RBAC package installed
- `User` model has no roles/permissions traits
- Need to add: `spatie/laravel-permission` + migration + User model trait

### Existing Patterns
- **Controllers**: Thin, action-method style. No FormRequest classes yet (inline validation).
- **Vue Pages**: Composition API + `<script setup lang="ts">`. `useForm()` from Inertia for forms. `AppLayout` wrapper with breadcrumbs.
- **Testing**: Pest 3.8. `RefreshDatabase` trait. Factory pattern (`User::factory()->create()`). `actingAs($user)` for auth.
- **UI Components**: 13 shadcn-vue component directories (Avatar, Breadcrumb, Button, Card, Checkbox, Collapsible, Dialog, DropdownMenu, Input, Label, NavigationMenu, Separator, Sheet, Sidebar, Skeleton, Tooltip).

### Inertia Page Resolution
- Auto-resolved: `resources/js/pages/{name}.vue` via `import.meta.glob`
- Layouts: `AppLayout.vue` (authenticated), `AuthLayout.vue` (guest), `settings/Layout.vue`
- Page naming follows directory structure (e.g., `'vacancies/Index'` resolves to `resources/js/pages/vacancies/Index.vue`)

## Gap Analysis (per module)

### 1. Vacancy Management (Gestión de Vacantes)
- **Exists**: Nothing
- **Needed**: Migration, Model, Controller (CRUD + status), FormRequest, Vue pages (Index, Create, Edit, Show), routes, sidebar entry, Pest tests

### 2. Applicant Management (Gestión de Aspirantes)
- **Exists**: Nothing
- **Needed**: Migration, Model (with file uploads for CV/certificates), Controller, FormRequest, Vue pages, routes, tests
- **Complexity**: Medium — file upload handling, document association

### 3. Interviews (Entrevistas)
- **Exists**: Nothing
- **Needed**: Migration (with date/time, type enum, status enum, interviewer FK), Model, Controller, Vue pages with calendar/date picker, routes, tests
- **Complexity**: Medium — scheduling logic, interviewer assignment

### 4. Tests/Tests (Pruebas)
- **Exists**: Nothing
- **Needed**: Migration (with type config, max_score), Model, Controller, Vue pages with dynamic form builder (numeric/text/multiple-choice), routes, tests
- **Complexity**: High — dynamic test type configuration, flexible scoring

### 5. Test Results (Resultados de Pruebas)
- **Exists**: Nothing
- **Needed**: Migration (applicant FK, test FK, score, observations, final status), Model, Controller, Vue pages, routes, tests
- **Complexity**: Medium — scoring logic, apt/no apt determination

### 6. Reports & Statistics (Reportes y Estadísticas)
- **Exists**: Nothing
- **Needed**: Controller (query-based), Vue pages with charts/tables, routes, tests
- **Complexity**: Medium — aggregation queries, comparison views, averages
- **Note**: May need a charting library (e.g., Chart.js via vue-chartjs)

## MySQL Migration

### What needs to change in `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_seleccion
DB_USERNAME=root
DB_PASSWORD=<secure_password>
```

Remove: `DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`

### What's already configured
- MySQL driver config already exists in `config/database.php` lines 45-63
- Uses standard env vars: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `utf8mb4` charset and collation are default

### Additional steps
1. Create the MySQL database: `CREATE DATABASE sistema_seleccion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
2. Run migrations: `php artisan migrate`
3. Update `phpunit.xml` if tests should use a separate MySQL test DB or keep SQLite in-memory

## Approaches

### Approach A: Module-by-Module (Sequential CRUD)
Build each module completely (migration → model → controller → pages → tests) before moving to the next. Start with foundational modules (Vacancies → Applicants → Tests → Interviews → Results → Reports).

- **Pros**: Each module is fully functional and testable independently. Natural dependency order. Easy to demo progress.
- **Cons**: Slower to see the full system. High risk of scope creep if starting module is complex.
- **Effort**: Medium-High (6 full-stack modules)

### Approach B: Data Layer First (All Migrations + Models → Then UI)
Build all database tables and models first (with factories and seeders), then implement all controllers and pages. This front-loads the data architecture.

- **Pros**: Complete data model from day one. Relationships are validated early. Seeders enable parallel frontend work.
- **Cons**: No working UI for a long time. Hard to validate business logic without views. Big bang integration risk.
- **Effort**: Medium

### Approach C: Vertical Slice per Role (Admin → Interviewer → Reports)
Build features grouped by user role. Start with Admin functionality (vacancy CRUD + test CRUD + user management), then Interviewer features (applicant registration + interviews + results), then Reports.

- **Pros**: Each slice delivers value to a real user group. Early RBAC validation. Stakeholder demos per role.
- **Cons**: Some modules (like Applicants) span both roles. May require refactoring shared components.
- **Effort**: Medium

### Recommendation
**Approach A (Module-by-Module)** is the safest for a fresh project. Build in dependency order:
1. RBAC foundation (spatie/laravel-permission, roles, seeders)
2. Vacancy Management
3. Applicant Management
4. Tests/Tests Management
5. Test Results
6. Interviews (depends on Applicants and Test Results)
7. Reports & Statistics (depends on all)

This ensures each phase has a working, testable deliverable and respects natural domain dependencies.

## Risks

| Risk | Severity | Mitigation |
|------|----------|-----------|
| Frontend test gap: no Vitest/Jest means Vue components can't be TDD'd | **Medium** | Install Vitest early; otherwise rely on Laravel Dusk or manual QA for frontend |
| Dynamic test types: variable scoring formats (numeric/text/multiple-choice) add significant UI complexity | **High** | Design the test engine as a separate subsystem with clear boundaries |
| File uploads: CV and certificate storage needs config (local vs S3) | **Low** | Use Laravel's filesystem abstraction; default to `local` disk |
| RBAC complexity: custom roles means dynamic permission management UI | **Medium** | Use spatie's built-in permission CRUD; limit custom role creation to Admin |
| MySQL switch: existing SQLite migrations may have compatibility issues | **Low** | Laravel's schema builder abstracts DB differences; test migration on MySQL early |
| No existing FormRequest pattern: validation strategy needs to be established | **Low** | Adopt FormRequest classes from the first module to set the standard |

## Ready for Proposal

**Yes.** The codebase is well-understood, the tech stack is appropriate, and all gaps are identified. Proceed to `sdd-propose`.

# RBAC Specification

## Purpose

Role-based access control using spatie/laravel-permission. Defines pre-seeded roles (Admin, Entrevistador) and allows Admin to create custom roles with granular permissions.

## Requirements

### Requirement: RBAC-001 — Pre-seeded Roles

The system SHALL provide two pre-seeded roles via database seeders: **Admin** (full access) and **Entrevistador** (limited access).

#### Scenario: Seeder creates default roles

- GIVEN a fresh database migration
- WHEN the role seeder runs
- THEN roles "Admin" and "Entrevistador" exist in the roles table
- AND each role has its associated permissions assigned

#### Scenario: Seeder is idempotent

- GIVEN the roles already exist
- WHEN the seeder runs again
- THEN no duplicate roles are created

### Requirement: RBAC-002 — Admin Full Access

The Admin role SHALL have all permissions across all modules (vacancies, applicants, tests, interviews, results, reports, roles).

#### Scenario: Admin accesses any route

- GIVEN a user with the Admin role
- WHEN the user requests any application route
- THEN access is granted

#### Scenario: Admin manages custom roles

- GIVEN a user with the Admin role
- WHEN the user navigates to role management
- THEN the user can create, edit, and delete custom roles

### Requirement: RBAC-003 — Entrevistador Limited Access

The Entrevistador role SHALL have permissions limited to: view vacancies, register/edit applicants, schedule interviews, record test results, and view scoped reports.

#### Scenario: Entrevistador cannot access admin-only routes

- GIVEN a user with the Entrevistador role
- WHEN the user requests vacancy creation or role management
- THEN access is denied with a 403 response

#### Scenario: Entrevistador can register applicants

- GIVEN a user with the Entrevistador role
- WHEN the user submits a valid applicant registration form
- THEN the applicant is created successfully

### Requirement: RBAC-004 — Custom Role CRUD

The system SHALL allow Admin users to create, edit, and delete custom roles by selecting individual permissions from the full permission set.

#### Scenario: Admin creates a custom role

- GIVEN an authenticated Admin user
- WHEN the Admin creates a role named "Supervisor" with permissions [view-applicants, view-interviews, edit-interviews]
- THEN the role is persisted with exactly those permissions

#### Scenario: Admin edits a custom role's permissions

- GIVEN a custom role "Supervisor" exists
- WHEN the Admin adds "edit-applicants" permission
- THEN the role's permissions are updated to include the new permission

#### Scenario: Admin cannot delete pre-seeded roles

- GIVEN the Admin attempts to delete the "Admin" or "Entrevistador" role
- WHEN the delete request is submitted
- THEN the system rejects the request with an error message

### Requirement: RBAC-005 — Middleware Enforcement

The system SHALL enforce permission checks via middleware on all protected routes.

#### Scenario: Unauthenticated user is redirected

- GIVEN an unauthenticated request
- WHEN the request targets a protected route
- THEN the user is redirected to the login page

#### Scenario: User without permission is denied

- GIVEN an authenticated user without the "edit-vacancies" permission
- WHEN the user sends a POST to the vacancy update endpoint
- THEN the system returns a 403 Forbidden response

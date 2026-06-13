# Vacancy Management Specification

## Purpose

CRUD operations for job vacancies with test configuration, evaluation criteria, and applicant listing per vacancy.

## Requirements

### Requirement: VAC-001 — Vacancy CRUD

The system SHALL allow Admin users to create, read, update, and delete vacancies with fields: position name, location, requirements (text), and status (open/closed/cancelled).

#### Scenario: Admin creates a vacancy

- GIVEN an authenticated Admin user
- WHEN the Admin submits a form with position="Developer", location="Remote", requirements="3+ years experience", status="open"
- THEN a vacancy is created with those fields and status defaults to "open"

#### Scenario: Admin closes a vacancy

- GIVEN an open vacancy exists
- WHEN the Admin updates the status to "closed"
- THEN the vacancy status is "closed" and new applicants cannot be assigned

#### Scenario: Entrevistador cannot create vacancies

- GIVEN an authenticated Entrevistador user
- WHEN the user sends a POST to the vacancy creation endpoint
- THEN the system returns a 403 Forbidden response

### Requirement: VAC-002 — Vacancy Status Lifecycle

The system SHALL enforce valid status transitions: open → closed, open → cancelled, closed → open. Cancelled vacancies SHALL NOT transition to any other status.

#### Scenario: Closed vacancy reopens

- GIVEN a vacancy with status "closed"
- WHEN the Admin updates status to "open"
- THEN the vacancy status becomes "open"

#### Scenario: Cancelled vacancy cannot change status

- GIVEN a vacancy with status "cancelled"
- WHEN any user attempts to change the status
- THEN the system rejects the request with a validation error

### Requirement: VAC-003 — Test Configuration per Vacancy

The system SHALL allow Admin to associate one or more tests to a vacancy and configure evaluation criteria: minimum grade threshold and weighted average formula.

#### Scenario: Admin configures tests for a vacancy

- GIVEN a vacancy exists
- WHEN the Admin associates tests [Technical Interview, Psychometric Test] with weights [60%, 40%] and min_grade=70
- THEN the vacancy stores the test associations with weights and the minimum grade threshold

#### Scenario: Weighted average formula is stored

- GIVEN a vacancy with configured tests and weights
- WHEN the vacancy is retrieved
- THEN the response includes the weighted average configuration (test IDs, weights, min_grade)

### Requirement: VAC-004 — List Applicants per Vacancy

The system SHALL display all applicants associated with a given vacancy, including their current status in that vacancy.

#### Scenario: View applicants for a vacancy

- GIVEN a vacancy with 3 associated applicants
- WHEN an authorized user views the vacancy detail page
- THEN the page lists all 3 applicants with their per-vacancy status

#### Scenario: Empty vacancy shows no applicants

- GIVEN a vacancy with no associated applicants
- WHEN an authorized user views the vacancy detail page
- THEN the page indicates no applicants are associated

### Requirement: VAC-005 — Entrevistador Vacancy View

The system SHALL allow Entrevistador users to view vacancies and their details but NOT create, edit, or delete them.

#### Scenario: Entrevistador views vacancy list

- GIVEN an authenticated Entrevistador user
- WHEN the user navigates to the vacancies index
- THEN the user sees all vacancies with their status

#### Scenario: Entrevistador cannot edit vacancy

- GIVEN an authenticated Entrevistador user
- WHEN the user sends a PUT request to update a vacancy
- THEN the system returns a 403 Forbidden response

# Applicant Management Specification

## Purpose

Applicant registration with document uploads, multi-vacancy association with independent status, admin blocking with assignment alerts, and applicant history timeline.

## Requirements

### Requirement: APP-001 — Applicant Registration

The system SHALL allow authorized users to register applicants with: full name, phone, email, and address.

#### Scenario: Interviewer registers an applicant

- GIVEN an authenticated Entrevistador user
- WHEN the user submits name="Juan Pérez", phone="+5491112345678", email="juan@email.com", address="Buenos Aires"
- THEN the applicant is created with those fields

#### Scenario: Duplicate email is rejected

- GIVEN an applicant with email "juan@email.com" exists
- WHEN a user attempts to register another applicant with the same email
- THEN the system rejects the request with a validation error

### Requirement: APP-002 — Document Uploads

The system SHALL allow uploading CV and certificates per applicant. Files MUST NOT exceed 5 MB. Allowed formats: PDF, DOCX, JPG, PNG.

#### Scenario: Upload valid CV

- GIVEN an applicant exists
- WHEN a user uploads a PDF file of 3 MB as CV
- THEN the file is stored and associated with the applicant as a CV document

#### Scenario: Oversized or invalid file is rejected

- GIVEN an applicant exists
- WHEN a user uploads a file >5 MB or with invalid format (.exe)
- THEN the system rejects with a validation error

### Requirement: APP-003 — Multi-Vacancy Association

The system SHALL allow one applicant to be associated with multiple vacancies. Each vacancy association SHALL have an independent status: registered, in-interview, evaluated, apt, no-apt.

#### Scenario: Applicant associated to two vacancies

- GIVEN an applicant exists and two open vacancies exist
- WHEN the user associates the applicant to both vacancies
- THEN the applicant has two independent vacancy associations, each with status "registered"

#### Scenario: Status change affects only one vacancy

- GIVEN an applicant is associated to vacancies A and B
- WHEN the user updates the applicant's status in vacancy A to "apt"
- THEN vacancy A shows status "apt" and vacancy B retains its original status

### Requirement: APP-004 — Applicant Blocking

The system SHALL allow Admin users to mark an applicant as blocked with a required block reason. The system MUST show an alert when attempting to assign a blocked applicant to any vacancy.

#### Scenario: Alert on blocked applicant assignment

- GIVEN a blocked applicant exists
- WHEN a user attempts to assign to a vacancy
- THEN the system shows alert with block reason and prevents assignment

#### Scenario: Only Admin can block

- GIVEN an Entrevistador user
- WHEN the user attempts to block an applicant
- THEN 403 Forbidden is returned

### Requirement: APP-005 — Applicant History Timeline

The system SHALL display a timeline of interviews and evaluations per applicant, ordered chronologically.

#### Scenario: View applicant history

- GIVEN an applicant with 2 completed interviews and 1 test result
- WHEN a user views the applicant's history page
- THEN the timeline shows all 3 events in chronological order with dates and details

#### Scenario: New applicant has empty history

- GIVEN a newly registered applicant with no interviews or results
- WHEN a user views the applicant's history page
- THEN the page indicates no history exists

### Requirement: APP-006 — Interviewer Applicant Visibility

The system SHALL allow Entrevistador users to view all applicants, with an optional filter to see only applicants assigned to them.

#### Scenario: View all applicants

- GIVEN an authenticated Entrevistador user
- WHEN the user navigates to the applicants index without filters
- THEN the user sees all applicants in the system

#### Scenario: Filter to assigned applicants only

- GIVEN an authenticated Entrevistador user with 3 self-assigned applicants
- WHEN the user applies the "assigned to me" filter
- THEN the list shows only the 3 assigned applicants

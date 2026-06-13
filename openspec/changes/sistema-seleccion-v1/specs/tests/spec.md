# Test Management Specification

## Purpose

CRUD of tests associated to vacancies with configurable types: numeric (score 0-N), text (open-ended with manual scoring), and multiple choice (predefined options with correct answers).

## Requirements

### Requirement: TST-001 — Test CRUD

The system SHALL allow Admin users to create, read, update, and delete tests. Each test has: name, description, type, max_score, and evaluation criteria.

#### Scenario: Admin creates or updates a test

- GIVEN an authenticated Admin user
- WHEN the Admin creates a test (name="Technical Score", type="numeric", max_score=100) or updates max_score to 50
- THEN the test is persisted/updated with those attributes

#### Scenario: Entrevistador cannot create tests

- GIVEN an authenticated Entrevistador user
- WHEN the user sends a POST to the test creation endpoint
- THEN the system returns a 403 Forbidden response

### Requirement: TST-002 — Numeric Test Type

The system SHALL support numeric tests where the score is a direct entry from 0 to max_score.

#### Scenario: Numeric test accepts valid score

- GIVEN a numeric test with max_score=100
- WHEN a user records a score of 85
- THEN the score is accepted and stored

### Requirement: TST-003 — Text Test Type

The system SHALL support text (open-ended) tests where the interviewer provides a manual score and textual observations.

#### Scenario: Text test stores score with observations

- GIVEN a text test exists
- WHEN a user records score=80 with observations="Good communication"
- THEN both score and observations are stored; auto-calculation is not available

### Requirement: TST-004 — Multiple Choice Test Type

The system SHALL support multiple choice tests with questions, options arrays, correct answer indices, and points per question.

#### Scenario: Admin creates a multiple choice test with questions

- GIVEN an authenticated Admin user
- WHEN the Admin creates a test with type="multiple-choice" and adds 3 questions (each with 4 options, 1 correct answer, 10 points)
- THEN the test is persisted with all questions and their configurations

#### Scenario: Multiple choice auto-calculates score

- GIVEN a multiple choice test with 3 questions worth 10 points each (max_score=30)
- WHEN a user selects 2 correct answers
- THEN the system calculates the score as 20

#### Scenario: Manual override of auto-calculated score

- GIVEN a multiple choice test with an auto-calculated score of 20
- WHEN a user manually overrides the score to 25
- THEN the overridden score is stored with a flag indicating manual adjustment

### Requirement: TST-005 — Test Reuse Across Vacancies

The system SHALL allow the same test template to be associated with multiple vacancies.

#### Scenario: Test associated to two vacancies

- GIVEN a test exists
- WHEN the Admin associates the test to vacancies A and B
- THEN the test is linked to both vacancies without duplication

#### Scenario: Editing a test template affects all vacancies

- GIVEN a test is associated to vacancies A and B
- WHEN the Admin updates the test's max_score
- THEN both vacancies reflect the updated test configuration

### Requirement: TST-006 — Test Association to Vacancy

The system SHALL allow Admin to associate tests to a vacancy with a weight percentage for the weighted average calculation.

#### Scenario: Associate test with weight

- GIVEN a vacancy and a test exist
- WHEN the Admin associates the test to the vacancy with weight=40
- THEN the vacancy-test association stores the weight of 40%

#### Scenario: Total weights must not exceed 100%

- GIVEN a vacancy already has tests with total weight=80%
- WHEN the Admin attempts to add a test with weight=30%
- THEN the system rejects the request with a validation error

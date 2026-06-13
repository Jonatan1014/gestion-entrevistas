# Test Results Specification

## Purpose

Score recording per applicant per test, system-calculated weighted average and minimum grade comparison, and human-determined final apt/no apt status per vacancy.

## Requirements

### Requirement: RES-001 — Record Score per Applicant per Test

The system SHALL allow authorized users to record a score for an applicant on a specific test. The score MUST be between 0 and the test's max_score.

#### Scenario: Record numeric test score

- GIVEN an applicant, a vacancy, and a numeric test (max_score=100)
- WHEN a user records a score of 75
- THEN the test result is stored with score=75

#### Scenario: Score out of range is rejected

- GIVEN a test with max_score=100
- WHEN a user attempts to record a score of 110 or -5
- THEN the system rejects with a validation error

### Requirement: RES-002 — Text Test Observations

The system SHALL allow storing textual observations alongside the score for text-type tests.

#### Scenario: Record text test with observations

- GIVEN a text test exists for an applicant
- WHEN a user records score=80 with observations="Demonstrates strong analytical thinking"
- THEN both the score and observations are persisted

### Requirement: RES-003 — Multiple Choice Auto-Calculation

The system SHALL automatically calculate the score for multiple choice tests based on correct answers, with optional manual override.

#### Scenario: Auto-calculate from correct answers

- GIVEN a multiple choice test with 5 questions at 10 points each
- WHEN a user submits answers with 4 correct
- THEN the system calculates and stores the score as 40

#### Scenario: Manual override with justification

- GIVEN an auto-calculated score of 40 exists
- WHEN a user overrides the score to 45
- THEN the overridden score is stored and flagged as manually adjusted

### Requirement: RES-004 — Weighted Average Calculation

The system SHALL calculate the weighted average score per applicant per vacancy using the formula configured in the vacancy's test configuration.

#### Scenario: Calculate weighted average

- GIVEN a vacancy with tests A (weight=60%, score=80) and B (weight=40%, score=90)
- WHEN the system calculates the weighted average
- THEN the result is 84.0 (80×0.6 + 90×0.4)

#### Scenario: Weighted average updates on new score

- GIVEN a vacancy with one test result recorded
- WHEN a second test score is recorded
- THEN the weighted average is recalculated including both scores

### Requirement: RES-005 — Minimum Grade Comparison

The system SHALL compare the calculated weighted average against the vacancy's minimum grade threshold and display the result as guidance.

#### Scenario: Score meets or below threshold

- GIVEN a vacancy with min_grade=70
- WHEN the system evaluates weighted averages of 84 and 65
- THEN it indicates "meets requirement" for 84 and "below threshold" for 65

### Requirement: RES-006 — Human-Determined Final Status

The system SHALL allow Entrevistador or Admin to set the final apt/no apt status per applicant per vacancy. The system SHALL display the calculated score as guidance but the human decision overrides it.

#### Scenario: Human overrides calculated guidance

- GIVEN an applicant with any weighted average
- WHEN a user sets status "apt" or "no-apt" with justification
- THEN the human decision is stored, overriding the calculated guidance

#### Scenario: Status is per vacancy per applicant

- GIVEN an applicant in vacancies A and B
- WHEN user sets "apt" for vacancy A
- THEN vacancy B's status remains unchanged

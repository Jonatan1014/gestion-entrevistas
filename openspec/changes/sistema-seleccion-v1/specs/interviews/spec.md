# Interview Management Specification

## Purpose

Interview scheduling with date/time, interviewer assignment (self + admin), status lifecycle (pending/completed/cancelled), type (virtual/presencial), and detailed observations.

## Requirements

### Requirement: INT-001 — Schedule Interview

The system SHALL allow authorized users to schedule interviews with: date, time, interviewer (FK to User), applicant (FK), vacancy (FK), type (virtual/presencial), link (for virtual), and location notes (for presencial).

#### Scenario: Schedule virtual or presencial interview

- GIVEN applicant, vacancy, and interviewer exist
- WHEN user schedules with type="virtual", link="https://meet.example/abc" or type="presencial", location_notes="Room 3"
- THEN the interview is created with those fields

#### Scenario: Virtual interview requires link

- GIVEN type="virtual" with empty link
- THEN the system rejects with a validation error

### Requirement: INT-002 — Interview Status Lifecycle

The system SHALL enforce interview status transitions: pending → completed, pending → cancelled. Completed and cancelled interviews SHALL NOT transition to any other status.

#### Scenario: Complete a pending interview

- GIVEN an interview with status "pending"
- WHEN a user marks it as "completed" with observations
- THEN the status becomes "completed"

#### Scenario: Cancel a pending interview

- GIVEN an interview with status "pending"
- WHEN a user cancels it with a cancellation reason
- THEN the status becomes "cancelled"

#### Scenario: Completed interview cannot be modified

- GIVEN an interview with status "completed"
- WHEN a user attempts to change the status
- THEN the system rejects the request with a validation error

### Requirement: INT-003 — Interviewer Self-Assignment

The system SHALL allow Entrevistador users to self-assign to applicants (and thus to their interviews).

#### Scenario: Interviewer self-assigns to an applicant

- GIVEN an unassigned applicant exists
- WHEN an Entrevistador user clicks "assign to me"
- THEN the applicant is associated with that interviewer

#### Scenario: Self-assignment creates interview context

- GIVEN an Entrevistador has self-assigned to an applicant
- WHEN the user views their interview list
- THEN the applicant appears in their assigned list

### Requirement: INT-004 — Admin Interviewer Assignment

The system SHALL allow Admin users to assign any interviewer to any applicant.

#### Scenario: Admin assigns or reassigns interviewer

- GIVEN an applicant exists (optionally assigned to interviewer A)
- WHEN the Admin assigns interviewer B
- THEN the applicant's interviewer is set to B

### Requirement: INT-005 — Interview Observations

The system SHALL allow interviewers to record detailed text observations for completed interviews.

#### Scenario: Record interview observations

- GIVEN a completed interview exists
- WHEN the interviewer adds observations="Candidate demonstrated strong technical skills"
- THEN the observations are stored and associated with the interview

#### Scenario: Observations are required for completion

- GIVEN a pending interview
- WHEN a user attempts to mark it as "completed" without observations
- THEN the system requires observations before allowing completion

### Requirement: INT-006 — Interview Listings

The system SHALL provide interview listings filtered by interviewer, by applicant, and by vacancy.

#### Scenario: View interviews by interviewer

- GIVEN an interviewer with 5 scheduled interviews
- WHEN the user views the interviewer's interview list
- THEN all 5 interviews are displayed with date, time, and applicant name

#### Scenario: View interviews by vacancy

- GIVEN a vacancy with 3 scheduled interviews
- WHEN the user views the vacancy's interview list
- THEN all 3 interviews are displayed

#### Scenario: View interviews by applicant

- GIVEN an applicant with 2 scheduled interviews across different vacancies
- WHEN the user views the applicant's interview history
- THEN both interviews are displayed with their respective vacancy names

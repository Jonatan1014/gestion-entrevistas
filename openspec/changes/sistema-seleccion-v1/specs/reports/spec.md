# Reports & Statistics Specification

## Purpose

Candidate comparison per vacancy, completed interview listings with filters, score averages and performance metrics, selection pipeline tracking, and PDF/Excel export.

## Requirements

### Requirement: RPT-001 — Candidate Comparison per Vacancy

The system SHALL display a table comparing all applicants for a given vacancy, showing test scores, weighted average, and final status.

#### Scenario: View candidate comparison table

- GIVEN a vacancy with 5 applicants, each with test results
- WHEN a user views the comparison report
- THEN the table shows all 5 applicants with their scores, weighted average, and apt/no apt status

#### Scenario: Comparison shows pending applicants

- GIVEN a vacancy with applicants who have no test results yet
- WHEN a user views the comparison report
- THEN those applicants appear with "pending" or "N/A" for scores

### Requirement: RPT-002 — Completed Interviews List with Filters

The system SHALL display completed interviews with filters by date range, interviewer, and vacancy.

#### Scenario: Filter by date range, interviewer, or vacancy

- GIVEN completed interviews exist across dates, interviewers, and vacancies
- WHEN a user applies any filter (date range, interviewer, or vacancy)
- THEN only matching interviews are displayed

### Requirement: RPT-003 — Score Averages per Test

The system SHALL calculate and display average scores per test, per vacancy.

#### Scenario: View average score per test

- GIVEN a test "Technical Interview" has been scored for 10 applicants in a vacancy
- WHEN a user views the test performance report
- THEN the report shows the average score across all 10 applicants

#### Scenario: Average excludes pending applicants

- GIVEN 10 applicants for a vacancy but only 6 have test results
- WHEN the average is calculated
- THEN only the 6 scored applicants are included in the calculation

### Requirement: RPT-004 — Selection Pipeline View

The system SHALL display a pipeline view per vacancy showing applicants at each stage: registered, in-interview, evaluated, apt, no-apt.

#### Scenario: Pipeline shows applicant counts per stage

- GIVEN a vacancy with applicants in various stages
- WHEN a user views the pipeline
- THEN each stage shows the count of applicants in that stage

#### Scenario: Pipeline updates on status change

- GIVEN an applicant in "in-interview" stage
- WHEN the applicant's status changes to "evaluated"
- THEN the pipeline reflects the updated counts

### Requirement: RPT-005 — PDF and Excel Export

The system SHALL export all reports to PDF and Excel (.xlsx) formats. Exports MUST respect current filters and include column headers.

#### Scenario: Export report to PDF

- GIVEN a report is displayed with optional filters applied
- WHEN the user clicks "Export PDF"
- THEN a PDF is downloaded with filtered data in table format

#### Scenario: Export report to Excel

- GIVEN a report is displayed
- WHEN the user clicks "Export Excel"
- THEN an .xlsx file is downloaded with headers in row 1 and filtered data

### Requirement: RPT-007 — Entrevistador Report Scope

The system SHALL restrict Entrevistador users to reports scoped to their own interviews only. Admin users SHALL have full access to all reports.

#### Scenario: Entrevistador sees scoped reports

- GIVEN an Entrevistador with 8 interviews
- WHEN the user views the candidate comparison report
- THEN only applicants from those 8 interviews are included

#### Scenario: Admin sees all reports

- GIVEN an Admin user
- WHEN the user views any report
- THEN all data across all interviewers and vacancies is visible

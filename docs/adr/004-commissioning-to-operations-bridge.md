# ADR-004: Commissioning-to-Operations Bridge via Weighted Readiness Scoring

## Status: Accepted
## Date: 2026-04-15

## Context

NexusOps bridges the gap between building commissioning (construction/handover phase)
and ongoing facility operations. The central question for every project is: "Is this
building ready for operational handover?" This decision has significant financial and
contractual implications -- premature handover means accepting a building with known
deficiencies, while delayed handover incurs carrying costs and schedule penalties.

We needed a quantitative readiness scoring algorithm that:
1. Provides a single 0-100 score for dashboard display and decision-making.
2. Reflects the relative importance of different readiness dimensions.
3. Is transparent enough for stakeholders to understand why a score is what it is.
4. Drives the handover decision workflow in the `Project` model.

## Decision

We implemented a **weighted composite score** with three dimensions, computed in
`Project::calculateReadinessScore()`:

| Dimension     | Weight | Source Data                                          |
|---------------|--------|------------------------------------------------------|
| Issues        | 40%    | `(total_issues - open_issues) / total_issues * 100`  |
| Tests         | 30%    | `completed_tests / total_tests * 100`                |
| Documents     | 30%    | `completed_closeout_docs / total_closeout_docs * 100` |

The final score is: `(issue_score * 0.4) + (test_score * 0.3) + (doc_score * 0.3)`,
rounded to two decimal places. When a dimension has zero total items (e.g., no tests
defined), it defaults to 100 to avoid penalizing projects that legitimately have no
items in that category.

## Rationale for Weights

### Issues at 40% (Highest Weight)
Open issues represent known deficiencies -- broken equipment, failed inspections,
non-conformances. In commissioning terminology, these map to the **Owner's Project
Requirements (OPR)** gap analysis. A building with unresolved issues is concretely
not ready for occupancy. Issues also correlate with the **Functional Performance
Testing (FPT)** phase: failed FPTs generate issues that must be resolved before
handover.

Issues receive the highest weight because they represent actual defects, not
administrative gaps. A building with 100% test completion and 100% documentation
but 20 open critical issues is categorically not ready.

### Tests at 30%
Tests represent the **Basis of Design (BOD)** verification -- proving that installed
systems perform to specification. Incomplete tests mean unverified systems, which is
a risk but not necessarily a defect. Some tests may be deferred post-handover with
a documented exception. This justifies a lower weight than issues.

### Documents at 30%
Closeout documents (as-built drawings, O&M manuals, warranty certificates, training
records) are contractually required for handover but do not indicate physical
deficiencies. A building with missing documents but no issues and complete tests is
operationally ready -- the documentation gap is administrative. Equal weight with
tests reflects that both represent completeness dimensions distinct from active defects.

## Alternatives Considered

### Equal Weights (33/33/33)
Rejected because it treats a missing O&M manual the same as an open critical HVAC
issue. Stakeholder interviews confirmed that operations teams care most about resolved
deficiencies, then verified performance, then paperwork.

### Configurable Per-Tenant Weights
Evaluated allowing tenants to customize weights via a settings table. Rejected for
MVP because:
- It introduces a configuration surface that requires UI, validation, and migration.
- Different weights across tenants make cross-tenant benchmarking meaningless.
- No pilot customer requested custom weights -- the 40/30/30 split aligned with all
  three pilot organizations' expectations.
- Can be added later without changing the scoring interface (just read weights from
  config instead of hardcoding).

### Binary Gate (Pass/Fail Thresholds)
Considered requiring 100% issue resolution, 95% test completion, and 90% documentation
as hard gates. Rejected because real-world handovers always have exceptions and
conditional acceptances. A continuous score gives stakeholders the information to make
nuanced decisions rather than imposing rigid cutoffs.

## How the Score Drives Handover

The `Project::getHandoverBlockers()` method complements the score by returning a
structured list of specific blockers (e.g., "12 open issues", "3 incomplete tests",
"5 missing closeout documents"). The dashboard displays both the aggregate score and
the blockers, enabling:

- **Score >= 95**: Green status, handover recommended. Minor items tracked as punch list.
- **Score 80-94**: Yellow status, conditional handover possible with documented exceptions.
- **Score < 80**: Red status, handover not recommended. Blockers must be addressed.

These thresholds are displayed in the UI but not enforced programmatically -- the
decision remains with the project manager, informed by the data.

## Future Evolution

1. **Weighted sub-categories**: Issues could be weighted by severity (critical issues
   count more than minor ones). This requires the `Issue` model to have a severity field.
2. **Trend scoring**: Factor in the rate of issue resolution and test completion to
   predict readiness dates.
3. **Per-tenant weight configuration**: When customer demand justifies the UI investment.

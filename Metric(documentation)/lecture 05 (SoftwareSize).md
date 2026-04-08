# Software Size — Lecture 05 Implementation
## Maternal Health Uganda | SENG 421

---

## What This Lecture Is About

Software size is not one single thing. Before you can estimate cost, plan timelines, or measure productivity, you need to know how big your software is. The lecture defines size across four dimensions:

```
Software Size
├── Length        → How many lines / tokens?
├── Functionality → What does it DO for the user?
├── Complexity    → How hard is it to understand?
└── Reuse         → How much was copied or reused?
```

---

## Dimension 1 — Length (LOC)

### What it is
Counting the physical lines of code in the source files.

- **NCLOC** — Non-Commented Lines of Code (actual working logic)
- **CLOC** — Commented Lines of Code
- **Total LOC = NCLOC + CLOC + blank lines**
- **Comment Density = CLOC / LOC × 100**

### Our measured results

| File | Total LOC | NCLOC | CLOC | Comment Density |
|---|---|---|---|---|
| metrics_logger.php | 495 | 417 | 35 | 7.07% |
| savetracker.php | 97 | 80 | 0 | 0.00% |
| login.php | 59 | 49 | 0 | 0.00% |
| signup.php | 57 | 47 | 0 | 0.00% |
| (15 files total) | 1,086 total | | | |

**Table:** `loc_measurements` | **Dashboard:** 📐 Software Size → LOC

---

## Dimension 1b — Halstead Metrics

A program is made of **operators** (if, =, +, while) and **operands** (variables, constants).

| Symbol | Meaning |
|---|---|
| μ1 | Distinct operators |
| μ2 | Distinct operands |
| N1 | Total operator occurrences |
| N2 | Total operand occurrences |

| Metric | Formula | Meaning |
|---|---|---|
| Vocabulary | μ = μ1 + μ2 | Total unique symbols |
| Length | N = N1 + N2 | Total symbols |
| Volume | V = N × log₂μ | Mental effort to write |
| Difficulty | D = (μ1/2) × (N2/μ2) | How hard to understand |
| Effort | E = V × D | Total mental effort |

| File | Effort E |
|---|---|
| metrics_logger.php | 126,453 |
| savetracker.php | 16,542 |
| login.php | 7,181 |

**Table:** `halstead_metrics` | **Dashboard:** 📐 Software Size → Halstead

---

## Dimension 2 — Function Points

Measures what the system DOES for users — independent of language and available at requirements stage.

| Type | What It Counts | Weight | Our Count | Weighted Value |
|---|---|---|---|---|
| EI — External Inputs | Data coming IN from users | 4 | 6 | 24 |
| EO — External Outputs | Data going OUT to users | 5 | 5 | 25 |
| EQ — External Queries | Question → response | 4 | 5 | 20 |
| ILF — Internal Logical Files | Data stored inside system | 10 | 6 | 60 |
| EIF — External Interface Files | Data from outside systems | 7 | 0 | 0 |
| **UFC Total** | | | | **129** |

**Table:** `function_points` | **Dashboard:** 📐 Software Size → Function Points

---

## Dimension 3 — Reuse

| Level | Definition |
|---|---|
| Verbatim | Copied with zero changes |
| Slightly modified | Fewer than 25% of lines changed |
| Extensively modified | 25% or more of lines changed |
| New | Written completely from scratch |

| File | Total LOC | Reuse Level |
|---|---|---|
| config.php | 30 | 83.33% (standard DB template) |
| signup.php | 57 | 17.54% |
| login.php | 59 | 16.95% |
| metrics_logger.php | 495 | 8.08% |

**Table:** `reuse_metrics` | **Dashboard:** 📐 Software Size → Reuse

---

## Files Added

| Item | Type | Purpose |
|---|---|---|
| `loc_measurements` | DB Table | LOC per file |
| `halstead_metrics` | DB Table | Halstead metrics per file |
| `function_points` | DB Table | Function point components |
| `reuse_metrics` | DB Table | Reuse classification per file |
| `backend/getsizemetrics.php` | PHP API | Serves all size data to dashboard |
| Dashboard tab 📐 Software Size | HTML/JS | Displays all four dimensions |


## Project Cost Estimation

## Team Size: 7 Members

---

## Step 1: Function Point Calculation

| Component | Count | Weight | Weighted Value | Our Items |
|---|---|---|---|---|
| External Inputs (EI) | 6 | 4 | 24 | Registration, Login, Tracker, Review, Feedback, Survey |
| External Outputs (EO) | 5 | 5 | 25 | Week result, Health tip, Dashboard, Experiments, Reviews |
| External Inquiries (EQ) | 5 | 4 | 20 | User profile, Tips, Survey results, Events, Experiments |
| Internal Logical Files (ILF) | 6 | 10 | 60 | Users, Tracking, Tips, Reviews, Surveys, Events |
| External Interface Files (EIF) | 0 | 7 | 0 | None |
| **UFC TOTAL** | **22** | — | **129** | |

```
UFC = 24 + 25 + 20 + 60 + 0 = 129

AFP = UFC x VAF = 129 x 1.0 = 129 Function Points
```

---

## Step 2: Total Effort Estimation

| Estimate | Formula | Total Hours | Total Person-Months |
|---|---|---|---|
| Low | 129 x 5 hrs/FP | 645 hrs | 3.7 months |
| Average | 129 x 10 hrs/FP | 1,290 hrs | 7.3 months |
| High | 129 x 15 hrs/FP | 1,935 hrs | 11.0 months |

> 1 person-month = 22 days x 8 hours = 176 hours

---

## Step 3: Effort Per Person (Team of 7)

Total effort is divided equally across 7 team members.

```
Hours per person  = Total Hours / 7  =  1,290 / 7  =  184 hours per person

Months per person = Total Months / 7  =  7.3 / 7  =  1.0 month per person

Project Duration  = 1.0 month  (all 7 working in parallel)
```

| Estimate | Total Hours | Hours Per Person | Duration |
|---|---|---|---|
| Low | 645 hrs | 92 hrs | ~0.5 months |
| Average | 1,290 hrs | 184 hrs | ~1.0 month |
| High | 1,935 hrs | 276 hrs | ~1.6 months |

---

## Step 4: Cost Per Person in USD

| Estimate | Hrs/Person | Junior ($12/hr) | Mid-Level ($30/hr) |
|---|---|---|---|
| Low | 92 hrs | $1,105.71 | $2,764.29 |
| Average | 184 hrs | $2,211.43 | $5,528.57 |
| High | 276 hrs | $3,317.14 | $8,292.86 |

---

## Step 5: Total Project Cost in USD

The total cost stays the same regardless of team size — it is just split across 7 people.

| Estimate | Total Hours | Total Cost (Junior) | Total Cost (Mid-Level) |
|---|---|---|---|
| Low | 645 hrs | $5,160 | $12,900 |
| Average | 1,290 hrs | $15,480 | $38,700 |
| High | 1,935 hrs | $29,025 | $77,400 |

---

## Step 6: Cost in UGX (1 USD = 3,700 UGX)

### Per Person

| Estimate | Per Person USD (Junior) | Per Person UGX (Junior) | Per Person UGX (Mid-Level) |
|---|---|---|---|
| Low | $1,105.71 | UGX 4,091,143 | UGX 10,227,857 |
| Average | $2,211.43 | UGX 8,182,286 | UGX 20,455,714 |
| High | $3,317.14 | UGX 12,273,429 | UGX 30,683,571 |

### Total Project

| Estimate | Total USD (Junior) | Total UGX (Junior) | Total UGX (Mid-Level) |
|---|---|---|---|
| Low | $5,160 | UGX 19,092,000 | UGX 47,730,000 |
| Average | $15,480 | UGX 57,276,000 | UGX 143,190,000 |
| High | $29,025 | UGX 107,392,500 | UGX 286,380,000 |

---

## Step 7: COCOMO Basic Model Cross-Check

> Total LOC = 1,086 → KLOC = 1.086 | Project Type = Organic | Team Size = 7

```
Total Effort  E = 2.4 x (1.086)^1.05 = 2.62 person-months

Duration with 7 people = E / 7 = 2.62 / 7 = 0.37 months ≈ 2 weeks

Team Size confirmed = 7 people
```

> COCOMO confirms that with 7 team members the project can be completed in under 1 month.

---

## Final Recommended Estimate (7-Person Team)

| Item | Value |
|---|---|
| **Total Development Hours** | **1,290 hours** |
| **Hours Per Person** | **184 hours** |
| **Project Duration** | **1 month (all 7 working in parallel)** |
| **Cost Per Person — USD (Junior)** | **$2,211** |
| **Cost Per Person — UGX (Junior)** | **UGX 8,182,286** |
| **Cost Per Person — USD (Mid-Level)** | **$5,529** |
| **Cost Per Person — UGX (Mid-Level)** | **UGX 20,455,714** |
| **Total Project Cost — USD (Junior)** | **$15,480** |
| **Total Project Cost — UGX (Junior)** | **UGX 57,276,000** |
| **Total Project Cost — USD (Mid-Level)** | **$38,700** |
| **Total Project Cost — UGX (Mid-Level)** | **UGX 143,190,000** |






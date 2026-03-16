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



 Project Cost Estimation
##  | Function Point Analysis + COCOMO |

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

## Step 2: Effort Estimation

| Estimate | Formula | Person-Hours | Person-Months |
|---|---|---|---|
| Low | 129 x 5 hrs/FP | 645 hrs | 3.7 months |
| Average | 129 x 10 hrs/FP | 1,290 hrs | 7.3 months |
| High | 129 x 15 hrs/FP | 1,935 hrs | 11.0 months |

> 1 person-month = 22 days x 8 hours = 176 hours

---

## Step 3: Cost in USD

| Estimate | Hours | Rate/hr | Cost (Junior $12) | Cost (Mid-Level $30) |
|---|---|---|---|---|
| Low | 645 | $8 – $20 | $5,160 | $12,900 |
| Average | 1,290 | $12 – $30 | $15,480 | $38,700 |
| High | 1,935 | $15 – $40 | $29,025 | $77,400 |

---

## Step 4: Cost in UGX (1 USD = 3,700 UGX)

| Estimate | Cost USD (Junior) | Cost UGX (Junior) | Cost UGX (Mid-Level) |
|---|---|---|---|
| Low | $5,160 | UGX 19,092,000 | UGX 47,730,000 |
| Average | $15,480 | UGX 57,276,000 | UGX 143,190,000 |
| High | $29,025 | UGX 107,392,500 | UGX 286,380,000 |

---

## Step 5: COCOMO Basic Model Cross-Check

> Total LOC = 1,086 → KLOC = 1.086 | Project Type = Organic (small team, simple system)

```
Effort   E = 2.4 x (1.086)^1.05 = 2.62 person-months

Dev Time D = 2.5 x (2.62)^0.38  = 3.60 months

Team Size  = E / D = 2.62 / 3.60 ≈ 1 person
```

> COCOMO confirms the Function Point low estimate — both give approximately 3.7 person-months for a solo developer.

---

## Final Recommended Estimate

| Item | Value |
|---|---|
| **Development Hours** | **1,290 hours (7.3 person-months)** |
| **Development Duration** | **4 – 6 months (2-person team)** |
| **Cost — USD (Junior)** | **$15,480** |
| **Cost — USD (Mid-Level)** | **$38,700** |
| **Cost — UGX (Junior)** | **UGX 57,276,000** |
| **Cost — UGX (Mid-Level)** | **UGX 143,190,000** |


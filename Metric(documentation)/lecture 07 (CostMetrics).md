# Lecture 07 — Software Cost Metrics

 

---



| Item | Detail |
|---|---|
| System name | Maternal Health Uganda |
| Technology | PHP 8.2, MySQL, JavaScript, HTML5, CSS3 — XAMPP on Windows |
| Team size | 7 developers |
| Backend files | 15 PHP files |
| Frontend files | 12 HTML/JS/CSS files |
| Database tables | 15 tables in maternal_health_uganda |
| Total LOC | 4,526 lines (backend 1,082 + frontend 3,444) |

---

## 2. Purpose of Cost Estimation

 We apply two estimation methods to the completed Maternal Health Uganda system:

- **Function Point Analysis** — counts what the system does for users, independent of programming language
- **COCOMO Basic Model** — uses actual lines of code to estimate effort and development time

Both methods use data measured directly from the codebase. Results are compared at the end to cross-validate the estimates.

---

## 3. Method 1 — Function Point Analysis

### 3.1 External Inputs (EI) — Weight 4

EIs are processes where users submit data that is stored or processed by the system.

| # | Component | Description | PHP File | Weight |
|---|---|---|---|---|
| 1 | User Registration | Mother submits fullname, email, password, gender — creates account in users table | signup.php | 4 |
| 2 | User Login | Mother submits email and password — system verifies credentials and starts session | login.php | 4 |
| 3 | Pregnancy Tracker Input | Mother submits Last Menstrual Period date and cycle length — system calculates current week and due date | savetracker.php | 4 |
| 4 | Review Submission | Mother submits 1–5 star rating and written review text — stored pending moderation | submitreview.php | 4 |
| 5 | Content Feedback | Mother submits feedback flagging an inaccurate health tip | submitfeedback.php | 4 |
| 6 | Survey Submission | Mother answers 5 Likert-scale satisfaction questions | submitsurvey.php | 4 |
| **Total EI** | **6 inputs** | **6 × 4 = 24 points** | | |

---

### 3.2 External Outputs (EO) — Weight 5

EOs are processes that send computed, derived, or transformed data back to the user.

| # | Component | Description | PHP File | Weight |
|---|---|---|---|---|
| 1 | Pregnancy Week Result | Computes current week (1–42), trimester (1–3), days remaining, and due date from LMP input | savetracker.php | 5 |
| 2 | Weekly Health Tip | Retrieves the specific health tip for the calculated pregnancy week from health_tips table | savetracker.php | 5 |
| 3 | GQM Metrics Dashboard | Computes all 8 quality indicator values (M1–M8) with live data from multiple tables | getmetrics.php | 5 |
| 4 | Experiment Statistical Results | Computes mean, standard deviation, min, max across all observations per experiment | computeresults.php | 5 |
| 5 | Approved Reviews Feed | Retrieves and formats approved reviews with star ratings and timestamps | getreviews.php | 5 |
| **Total EO** | **5 outputs** | **5 × 5 = 25 points** | | |

---

### 3.3 External Inquiries (EQ) — Weight 4

EQs are simple request-response interactions — user requests data and receives it directly with no calculation.

| # | Component | Description | PHP File | Weight |
|---|---|---|---|---|
| 1 | User Profile Lookup | Returns logged-in user fullname, email, gender, and role | getuserdata.php | 4 |
| 2 | Health Tips List | Returns all 42 tips with review status for admin dashboard | getmetrics.php | 4 |
| 3 | Survey Results | Returns aggregated responses per question for admin reporting | getmetrics.php | 4 |
| 4 | Case Study Events | Returns all logged user events from case_study_events table | getexperiments.php | 4 |
| 5 | Experiments List | Returns all experiments with status and observation counts | getexperiments.php | 4 |
| **Total EQ** | **5 inquiries** | **5 × 4 = 20 points** | | |

---

### 3.4 Internal Logical Files (ILF) — Weight 10

ILFs are the major persistent data groupings maintained by the system.

| # | Table | Data It Stores | Weight |
|---|---|---|---|
| 1 | users | Registered mother accounts — credentials, role, activity dates | 10 |
| 2 | pregnancy_tracking | Tracker history per user — LMP date, due date, week, trimester | 10 |
| 3 | health_tips | 42 curated weekly health tips with medical review status | 10 |
| 4 | reviews | Community reviews — rating, text, approval status | 10 |
| 5 | survey_responses | Satisfaction survey submissions — 5 Likert scores per response | 10 |
| 6 | case_study_events | Automatically logged user activity events for empirical investigation | 10 |
| **Total ILF** | **6 tables** | **6 × 10 = 60 points** | |

---

### 3.5 External Interface Files (EIF) — Weight 7

Maternal Health Uganda is a fully standalone system. It does not connect to any external APIs, government health registries, or third-party services. All data is managed internally.

**EIF = 0 × 7 = 0 points**

---

### 3.6 UFC Calculation

| Component Type | Count | Weight | Sub-total |
|---|---|---|---|
| External Inputs (EI) | 6 | 4 | 24 |
| External Outputs (EO) | 5 | 5 | 25 |
| External Inquiries (EQ) | 5 | 4 | 20 |
| Internal Logical Files (ILF) | 6 | 10 | 60 |
| External Interface Files (EIF) | 0 | 7 | 0 |

```
UFC = 24 + 25 + 20 + 60 + 0 = 129 Function Points
```

---

### 3.7 Value Adjustment Factor (VAF) and AFP

The VAF adjusts the UFC for the technical complexity of the system. It is derived from 14 General System Characteristics (GSCs), each rated 0–5. For Maternal Health Uganda, all 14 GSCs are at nominal level (sum = 35) because it is a standard PHP web application with no real-time constraints, no distributed processing, and no complex interfaces.

```
VAF = 0.65 + (0.01 × Σ GSC ratings)
VAF = 0.65 + (0.01 × 35) = 0.65 + 0.35 = 1.0
```

```
AFP = UFC × VAF = 129 × 1.0 = 129 Adjusted Function Points
```

> VAF = 1.0 confirms the system is of average technical complexity — a straightforward PHP/MySQL web application with no unusual constraints.

---

### 3.8 Converting AFP to Effort and Cost

PHP web applications typically require 5 to 15 development hours per Function Point:

| Estimate | Hrs/FP | Formula | Total Hours | Person-Months |
|---|---|---|---|---|
| Low | 5 hrs | 129 × 5 | 645 hours | 3.7 PM |
| Average | 10 hrs | 129 × 10 | 1,290 hours | 7.3 PM |
| High | 15 hrs | 129 × 15 | 1,935 hours | 11.1 PM |

> 1 person-month = 22 days × 8 hours = 176 hours. Average estimate (1,290 hours) is the primary figure.

**Distributing across 7 team members:**

```
Hours per person = 1,290 ÷ 7 = 184.3 hours
Duration = 7.3 months ÷ 7 = 1.04 months ≈ 1 month per person
```

**Cost estimate (1 USD = 3,700 UGX, March 2026):**

| Developer Level | Rate | Cost/Person (USD) | Total USD (7 people) | Total UGX |
|---|---|---|---|---|
| Junior Developer | $12/hr | $2,211 | $15,480 | UGX 57,276,000 |
| Mid-Level Developer | $30/hr | $5,529 | $38,700 | UGX 143,190,000 |

**Calculation for junior rate:**
```
$12/hr × 184.3 hrs = $2,211.43 per person
$2,211.43 × 7 = $15,480
$15,480 × 3,700 = UGX 57,276,000
```

---

## 4. Method 2 — COCOMO Basic Model

### 4.1 Measuring the Codebase

| PHP File | Total LOC | Blank | Comment | NCLOC | Role |
|---|---|---|---|---|---|
| metrics_logger.php | 495 | 43 | 124 | 328 | GQM + empirical engine |
| savetracker.php | 97 | 17 | 0 | 80 | Pregnancy calculator |
| login.php | 59 | 10 | 0 | 49 | Authentication |
| signup.php | 57 | 10 | 0 | 47 | Registration |
| getexperiments.php | 54 | 9 | 9 | 36 | Experiment data API |
| computeresults.php | 42 | 7 | 6 | 29 | Statistics engine |
| submitsurvey.php | 46 | 6 | 7 | 33 | Survey submission |
| getuserdata.php | 38 | 7 | 4 | 27 | User profile API |
| submitfeedback.php | 42 | 8 | 3 | 31 | Feedback handler |
| config.php | 30 | 2 | 2 | 26 | Database connection |
| Other 5 files | 120 | 19 | 8 | 93 | Logout, reviews, log... |
| **TOTAL — Backend** | **1,082** | **138** | **163** | **781** | |

```
Backend KLOC = 1,082 / 1,000 = 1.082 KLOC
```

Including frontend HTML, JavaScript, and CSS files (3,444 lines):
```
Total LOC = 1,082 + 3,444 = 4,526
Total KLOC = 4,526 / 1,000 = 4.526 KLOC
```

---

### 4.2 Development Mode — Organic

Our project is Organic because:

- Team of 7 — small, well-acquainted group of undergraduate students
- Familiar domain — PHP/MySQL web development, well understood by the team
- Familiar environment — XAMPP, no unusual tools or constraints
- No extreme requirements — no real-time processing, no hardware integration, no safety-critical constraints

**Organic mode formula:** `E = 2.4 × (KLOC)^1.05`

---

### 4.3 Effort Calculation

**Using Backend KLOC = 1.082:**
```
E = 2.4 × (1.082)^1.05
E = 2.4 × 1.087 = 2.61 person-months
```

**Using Total KLOC = 4.526:**
```
E = 2.4 × (4.526)^1.05
E = 2.4 × 4.817 = 11.56 person-months
```

---

### 4.4 Development Time and Team Size

**Formula:** `D = 2.5 × E^0.38`

**Backend only (E = 2.61):**
```
D = 2.5 × (2.61)^0.38 = 2.5 × 1.439 = 3.60 months
Team size = E / D = 2.61 / 3.60 = 0.73 ≈ 1 person
```

**Full project (E = 11.56):**
```
D = 2.5 × (11.56)^0.38 = 2.5 × 2.371 = 5.93 months
Team size = E / D = 11.56 / 5.93 = 1.95 ≈ 2 people
```

---

### 4.5 COCOMO Results Summary

| Measure | Backend Only | Full Project | With 7 Team | Formula |
|---|---|---|---|---|
| KLOC input | 1.082 | 4.526 | — | Measured |
| Effort E (PM) | 2.61 | 11.56 | — | 2.4 × KLOC^1.05 |
| Duration D (months) | 3.60 | 5.93 | — | 2.5 × E^0.38 |
| Team size | 0.73 | 1.95 | 7 members | E / D |
| Hours/person (7) | 65.7 | 290.9 | avg of both | ÷ 7 |

---

## 5. Comparison and Final Estimate

| Item | Function Points | COCOMO (full project) |
|---|---|---|
| Input | 129 AFP | 4.526 KLOC |
| Effort | 7.3 person-months | 11.56 person-months |
| Per person (7) | 184 hrs, ~1 month | 291 hrs, ~1.6 months |
| Total hours | 1,290 hrs | 2,034 hrs |
| Cost (Junior, UGX) | UGX 57,276,000 | UGX 90,319,200 |
| What it covers | Full lifecycle: design, code, test, docs | Proportional to all source files |

Both estimates place the commercial cost of this system between **UGX 57 million and UGX 90 million** for a junior team of 7, and between **UGX 143 million and UGX 226 million** at mid-level rates.

> **Recommended estimate: UGX 57,276,000 (junior team of 7, average FP rate of 10 hrs/FP).** Function Point Analysis is the more appropriate primary estimate because it measures what the system delivers to users, not just how many lines of code it has.

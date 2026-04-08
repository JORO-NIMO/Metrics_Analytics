# Empirical Investigation — Chapter 4 Implementation
## Maternal Health Uganda | SENG 421 Software Quality Assurance

---

## 1. Overview

Empirical investigation means applying scientific principles to study whether software tools and techniques actually work in practice. Rather than assuming the Maternal Health Uganda platform is useful, we design real investigations using real user data to confirm or refute that assumption.

**Purpose of our investigation:**
- Evaluate the usability and performance of the platform for expectant mothers
- Validate the accuracy of the pregnancy tracker
- Understand how users interact with health tips and features over time

---

## 2. Investigation Principles Applied

The lecture defines four core principles for any empirical investigation. Here is how each applies to our project.

**1. Stating the Hypothesis**
We form a clear, testable statement before collecting any data.

> Hypothesis: Showing a personalised health tip immediately after pregnancy tracking increases user return visits within 7 days.

**2. Selecting the Investigation Technique**
We use all three techniques — formal experiment, case study, and survey — at different stages of the project lifecycle.

**3. Maintaining Control Over Variables**
We identify which variables we change (independent) and which we measure as a result (dependent), and keep all other variables constant.

**4. Making the Investigation Meaningful**
Every investigation ties back to a GQM goal from Chapter 3 so that results are always actionable.

---

## 3. The Three Investigation Techniques

### Technique 1 — Formal Experiment

**Definition (from lecture):** A controlled investigation of an activity, by identifying, manipulating and documenting key factors of that activity.

**Our hypothesis:**
> Displaying a health tip immediately after the user runs the pregnancy tracker increases their likelihood of returning to the platform within 7 days.

**Independent variable:** Whether a health tip is displayed after tracking (yes or no)

**Dependent variable:** 7-day return visit rate (measured via `users.last_login`)

**How we control it:** All other variables are kept constant — same platform, same users, same time period. Only the tip display is changed between the two groups.

**How it is implemented in the project:**

| Field | Detail |
|---|---|
| Database table | `experiments` — stores the experiment definition |
| Observations table | `experiment_observations` — one row per user action recorded |
| Group assignment | `group_name` column — values: `control` (no tip) or `treatment` (tip shown) |
| Blocking | `block_name` column — groups users by first-time vs returning mothers |
| Contact degree | Second degree — system monitors automatically via `backend/savetracker.php` |
| Backend file | `backend/savetracker.php` logs each tracker use; `backend/computeresults.php` computes results |

**Principles followed:**

- **Replication:** Every time any user runs the tracker, a new observation row is recorded. The experiment runs continuously, not just once.
- **Randomisation:** Users are assigned to control or treatment group via the `group_name` field when they first register.
- **Local Control (Blocking):** Users are grouped by experience (first-time pregnancy vs previous pregnancies) so that prior experience does not skew the return visit results.

---

### Technique 2 — Case Study

**Definition (from lecture):** Document an activity by identifying key factors (inputs, constraints and resources) that may affect the outcomes of that activity.

**What we document:** Real usage of the Maternal Health Uganda platform from the moment it launched — logins, tracker uses, errors, feature interactions, and content feedback. We do not control anything. We observe what actually happens.

**Our case study focus:**
> Document how mothers use the pregnancy tracker from first registration through to their due date, capturing where they drop off, what errors they encounter, and which health tips they engage with most.

**How it is implemented in the project:**

| Field | Detail |
|---|---|
| Database table | `case_study_events` — every significant user action is recorded here automatically |
| Event categories | `feature_use`, `error_encounter`, `user_feedback` |
| Contact degree | Second degree — real-time monitoring via `MetricsLogger::logCaseStudyEvent()` |
| Backend file | `backend/metrics_logger.php` — `logCaseStudyEvent()` method called from login, signup, tracker, and review submission |

**Key events recorded in `case_study_events`:**

| Event Name | Triggered By | What It Documents |
|---|---|---|
| User Login Success | `backend/login.php` | Mothers actively returning to the platform |
| Pregnancy Tracker Used | `backend/savetracker.php` | Feature usage and calculation accuracy |
| New User Registered | `backend/signup.php` | Registration funnel conversion |
| Content Feedback Submitted | `backend/submitfeedback.php` | Mothers reporting inaccurate health tips |
| Login Failed — Wrong Password | `backend/login.php` | Authentication issues affecting usability |

**Variables documented (inputs and constraints):**
- User role (admin or regular mother)
- Pregnancy week at time of each interaction
- Impact level of each event (low, medium, high)
- Related GQM metric (e.g. M1.1 for tracker use, M5.1 for login failure)

---

### Technique 3 — Survey

**Definition (from lecture):** A retrospective study of a situation to try to document relationships and outcomes.

**What we survey:** After mothers have used the platform, we ask them to rate their experience across five dimensions that map directly to our GQM goals.

**Our survey hypothesis:**
> Mothers who used the pregnancy tracker weekly rate overall platform satisfaction higher than those who used it less frequently.

**How it is implemented in the project:**

| Field | Detail |
|---|---|
| Frontend file | `frontend/survey.html` — 5-question Likert scale form (1–5 stars) |
| Database table | `survey_responses` — one row per completed survey |
| Contact degree | First degree — mothers directly fill in the form themselves |
| Backend file | `backend/submitsurvey.php` — saves responses to the database |

**The 5 survey questions and their GQM link:**

| Question | What It Measures | Linked GQM Goal |
|---|---|---|
| Was the pregnancy tracker useful? | Tracker accuracy and usefulness | MG1 — Tracker Accuracy |
| Were the health tips accurate? | Content quality | MG7 — Content Inaccuracies |
| Was the site easy to use? | Usability and responsiveness | MG6 — System Reliability |
| Would you recommend this platform? | Overall satisfaction | MG4 — Return Visits |
| Overall satisfaction (1–5) | General user experience | GA — Enhance User Experience |

---

## 4. Comparison of Techniques Used

| Factor | Formal Experiment | Case Study | Survey |
|---|---|---|---|
| When used | During development testing | Throughout live usage | After months of usage |
| Level of control | High — we control which group sees tips | Low — we only observe | None — users report freely |
| Contact degree | Second (automated monitoring) | Second (automated logging) | First (direct user input) |
| What it produces | Proof that a feature works or does not | Real usage patterns and drop-off points | User perception and satisfaction scores |
| Database table | `experiment_observations` | `case_study_events` | `survey_responses` |

---

## 5. Variables

### Independent Variables (things we change on purpose)

| Variable | Where Controlled | Values |
|---|---|---|
| Health tip display after tracking | `group_name` in `experiment_observations` | `control` or `treatment` |
| User group (blocking) | `block_name` in `experiment_observations` | `first_time` or `returning` |

### Dependent Variables (things we measure as a result)

| Variable | Where Measured | Linked Metric |
|---|---|---|
| 7-day return visit rate | `users.last_login` | M3 — Session Duration |
| Tracker completion rate | `pregnancy_tracking.current_week` | M5 — Completion Rate |
| User satisfaction score | `survey_responses.q_overall_satisfy` | Survey results |
| Content inaccuracy reports | `content_feedback` table | M2 — Content Ratings |

---

## 6. Contact Degrees Applied

| Degree | Definition | How We Use It |
|---|---|---|
| First degree | Direct access to participants | `survey.html` — mothers fill in the form themselves |
| Second degree | Access to work environment, real-time monitoring | `logpageview.php`, `logAuthEvent()`, `logTrackerUse()` — automatic silent logging |
| Third degree | Access to work artefacts and logs after the fact | Admin dashboard queries `case_study_events`, `experiment_observations`, `survey_responses` for historical analysis |

---

## 7. Empirical Research Guidelines Applied

These are the coded guidelines from the lecture. Below is how each one is satisfied in our project.

### Context (C)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| C1 | Specify context — entities, attributes, measures | Every logged event includes: entity (user), attribute (action type), measure (timestamp, week number, impact level) |
| C2 | State hypothesis clearly before the study | Our hypothesis is stated in the `experiments` table under the `title` and `hypothesis` columns before any data is collected |
| C3 | For exploratory investigations, state the questions being addressed | The case study documents open questions: where do mothers drop off? Which tips are ignored? |

### Experimental Design (D)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| D1 | Identify the population | Registered mothers on the Maternal Health Uganda platform (stored in `users` table) |
| D2 | Define subject selection criteria | Active users with `is_active = 1`; must have used the tracker at least once |
| D3 | Define how subjects are assigned to treatments | Random assignment via `group_name` field on first tracker use |
| D5 | Define the experimental unit | One pregnancy tracking session per user per day |

### Data Collection (DC)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| DC1 | Define all measures fully — entity, attribute, unit, counting rule | Every metric in Chapter 3 specifies entity + attribute + scale + unit (e.g. M5: entity = user, attribute = completion, unit = %, counting rule = weeks 1–12 + 13–26 + 27–40 all present) |
| DC2 | Describe quality control for data collection | All logging functions use try-catch blocks; failed logs are written to `system_errors` table |
| DC3 | Record data about subjects who drop out | Users who stop using the tracker mid-pregnancy are still in `pregnancy_tracking` with their last recorded week — drop-off is measurable |

### Analysis (A)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| A4 | Ensure data does not violate test assumptions | Ratio-scale metrics (load time, completion %) use mean and standard deviation; ordinal metrics (star ratings) use median |
| A5 | Apply quality control to verify results | `backend/computeresults.php` computes mean, std deviation, min, max per group and stores results in `experiment_results` table |

### Presentation of Results (P)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| P2 | Present quantitative results with significance levels | Admin dashboard shows numeric values for all 8 GQM indicators with target thresholds |
| P4 | Provide descriptive statistics | `experiment_results` table stores n, mean, std deviation, min, max per group |

### Interpretation (I)

| Code | Guideline | How We Satisfy It |
|---|---|---|
| I2 | Differentiate statistical significance from practical importance | Dashboard flags when an indicator is below target (e.g. return visit rate < 40%) rather than just showing the raw number |
| I3 | Specify limitations | Survey is voluntary so responses may not represent all users; case study data is limited to users who consented to data collection via registration |

---

## 8. Investigation Summary

| Technique | Hypothesis | Independent Variable | Dependent Variable | Where Stored |
|---|---|---|---|---|
| Formal Experiment | Showing a health tip after tracking increases 7-day return visits | Tip display on or off (`group_name`) | Return visit rate (`last_login`) | `experiment_observations`, `experiment_results` |
| Case Study | Document real platform usage from launch to due date | None — observation only | All user actions and events | `case_study_events` |
| Survey | Weekly tracker users rate satisfaction higher than infrequent users | Tracker usage frequency | Overall satisfaction score | `survey_responses` |

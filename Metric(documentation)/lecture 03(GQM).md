# Goal-Based Metrics — GQM Implementation
## Maternal Health Uganda | SENG 421 Software Quality Assurance

---

## Concept: Goal-Based Metrics

The Goal-Based Metrics concept centres on defining measurable objectives before collecting any data. Instead of tracking arbitrary statistics, every metric is tied directly to a specific goal, ensuring that measurement effort produces actionable quality information.

| Field | Detail |
|---|---|
| Concept | Goal-Based Metrics |
| Paradigm Chosen | Goal-Question-Metrics (GQM) |
| Origin | Basili & Rombach (1988) — Software Engineering Institute |
| Purpose | Align software measurement with project goals so that every data point collected answers a real quality question |
| Applied To | Maternal Health Uganda — Web-based maternal care platform (PHP + MySQL) |

---

## Goal

**Goal A (GA): Enhance User Experience for Expectant Mothers on the Platform**

This goal drives all measurement activity. A positive user experience on a maternal health platform directly translates to better health outcomes — mothers who find the app easy, fast, and relevant are more likely to use it consistently throughout their pregnancy.

---

## Goal Questions

The following five questions operationalise Goal A. Each question identifies a specific quality attribute that must be investigated to determine whether the goal is being achieved.

**Q1 — Which health tips and features do users engage with most?**
Identifies which parts of the platform are most valuable to mothers, guiding content and feature prioritisation.

**Q2 — Are there specific health topics or tracker features users wish we offered more of?**
Reveals gaps in platform content or functionality that reduce satisfaction for expectant mothers.

**Q3 — Do users find the featured health tips and pregnancy advice relevant to their stage of pregnancy?**
Determines whether week-specific content recommendations are accurate and useful.

**Q4 — How do users rate the platform's loading speed and responsiveness when browsing tips and using the tracker?**
Assesses technical performance quality, which directly impacts whether mothers abandon the platform mid-session.

**Q5 — How often do users return to the platform and complete their pregnancy tracking journey?**
Measures sustained engagement — a proxy for whether the platform delivers ongoing value throughout pregnancy.

---

## Metrics for Achieving the Goal

Five metrics are defined to answer the goal questions. Each metric is directly traceable to one or more questions, ensuring no data is collected without purpose.

| Code | Metric | Description |
|---|---|---|
| M1 | Feature Views | Tracks how many times each feature or health tip is opened |
| M2 | Content Ratings | Captures user star ratings (1–5) for health tips and platform features |
| M3 | Session Duration | Measures total time a user spends per session on the platform |
| M4 | Page Load Times | Evaluates the speed at which pages and content load |
| M5 | Completion Rate | Measures how often users complete their pregnancy tracking journey |

---

## How We Have Implemented the Metrics

Each metric is implemented directly in the Maternal Health Uganda PHP + MySQL codebase.

---

### M1 — Feature Views (adapted from Book Views)

| Field | Detail |
|---|---|
| Linked Questions | Q1, Q3 |
| How It Is Tracked | Each time a user opens a health tip or uses the pregnancy tracker, the view count for that feature is incremented by 1 in the database via `backend/logpageview.php` |
| Database Table | `page_views` — stores: page name, session ID, user ID, timestamp |
| Attribute | View count per feature or health tip |
| Scale | Ratio scale |
| Unit | Count (number of views) |
| Target | Identify top 5 most-viewed features per week; flag tips with 0 views in 30 days |

---

### M2 — Content Ratings (adapted from Book Ratings)

| Field | Detail |
|---|---|
| Linked Questions | Q1, Q2, Q3 |
| How It Is Tracked | Users rate health tips and the platform using a 1–5 star system. Ratings are stored via `backend/submitreview.php` and `backend/submitfeedback.php` |
| Database Table | `reviews` — stores: user_id, review_text, rating (1–5), is_approved, created_at |
| Attribute | Star rating per piece of content (1–5 stars) |
| Scale | Ordinal scale |
| Unit | Stars (1–5) |
| Target | Average rating ≥ 4.0 stars; content rated below 3.0 flagged for review within 48 hours |

---

### M3 — Session Duration

| Field | Detail |
|---|---|
| Linked Questions | Q5 |
| How It Is Tracked | The system logs when a user logs in (session start via `backend/login.php` updating `last_login`) and records each activity event with a timestamp in `case_study_events`, allowing session duration to be computed |
| Database Table | `case_study_events` — stores: event_category, event_name, user_id, occurred_at |
| Attribute | Duration per session |
| Scale | Ratio scale |
| Unit | Minutes |
| Target | Average session duration ≥ 5 minutes; sessions under 30 seconds flagged as bounces |

---

### M4 — Page Load Times

| Field | Detail |
|---|---|
| Linked Questions | Q4 |
| How It Is Tracked | A timestamp is recorded when a user requests a page (`performance.now()` on the client side) and another when the page fully loads. The delay is sent to `backend/logpageview.php` and stored alongside the page view record |
| Database Table | `page_views` — extended with load_time_ms column; `system_errors` logs critical slowness events |
| Attribute | Load time per page request |
| Scale | Ratio scale |
| Unit | Seconds (milliseconds stored internally) |
| Target | 95% of pages load in under 2 seconds; any page exceeding 5 seconds triggers a `system_errors` entry with severity = high |

---

### M5 — Completion Rate

| Field | Detail |
|---|---|
| Linked Questions | Q5 |
| How It Is Tracked | The system logs when a user first uses the pregnancy tracker (week 1 saved via `backend/savetracker.php`) and tracks whether they return each trimester. A user is counted as complete if they have tracker records across all three trimesters (weeks 1–12, 13–26, 27–40) |
| Database Table | `pregnancy_tracking` — stores: user_id, last_period_date, current_week, due_date; `tracker_logs` — stores every individual use event |
| Attribute | Completion percentage |
| Scale | Ratio scale |
| Unit | Percentage (%) |
| Target | Completion rate ≥ 60% of registered users; drop-off points identified per trimester transition |

---

## Question-to-Metric Mapping

| Metric Code | Metric Name | Linked Questions | Goal Addressed |
|---|---|---|---|
| M1 | Feature Views | Q1, Q3 | GA — Enhance User Experience |
| M2 | Content Ratings | Q1, Q2, Q3 | GA — Enhance User Experience |
| M3 | Session Duration | Q5 | GA — Enhance User Experience |
| M4 | Page Load Times | Q4 | GA — Enhance User Experience |
| M5 | Completion Rate | Q5 | GA — Enhance User Experience |

---

## GQM Hierarchy Summary

```
Goal A (GA)
Enhance User Experience for Expectant Mothers
│
├── Q1  Which features do users engage with most?
│       └── M1 (Feature Views)   M2 (Content Ratings)
│
├── Q2  What topics do users wish we offered more of?
│       └── M2 (Content Ratings)
│
├── Q3  Is featured content relevant to the user's pregnancy stage?
│       └── M1 (Feature Views)   M2 (Content Ratings)
│
├── Q4  How fast and responsive is the platform?
│       └── M4 (Page Load Times)
│
└── Q5  Do users return and complete their tracking journey?
        └── M3 (Session Duration)   M5 (Completion Rate)
```

---

## Database Tables Supporting the Metrics

| Table | Key Columns | Supports Metric(s) |
|---|---|---|
| `page_views` | page, session_id, user_id, created_at | M1, M4 |
| `reviews` | user_id, review_text, rating, is_approved, created_at | M2 |
| `case_study_events` | event_category, event_name, user_id, occurred_at | M3 |
| `system_errors` | endpoint, error_msg, severity, created_at | M4 |
| `pregnancy_tracking` | user_id, last_period_date, current_week, due_date | M5 |
| `tracker_logs` | user_id, calculated_week, is_error, created_at | M1, M5 |

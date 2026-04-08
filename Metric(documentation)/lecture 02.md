
# MATERNAL HEALTH UGANDA
### Web Application — Key Performance Metrics

---

## Overview

This document provides an overview of the key performance metrics for the Maternal Health Uganda web application. The system is a PHP/MySQL and JavaScript platform designed to support expectant mothers in Uganda through pregnancy tracking, health resources, user authentication, and community reviews.

These metrics demonstrate the application of Nominal, Ordinal, Interval, Ratio, and Absolute scale types to real software measurement data. For every metric, the formal measurement model **m = (attribute, scale type, unit)** is applied. The scale type determines which mathematical operations are valid — applying the wrong operation to the wrong scale produces results that appear meaningful but are mathematically invalid.

---

## 1. Size Metrics — Lines of Code

Lines of Code (LOC) is a direct measure of program size on the **Ratio scale**. It has a genuine zero point and ratios between values are fully meaningful. The concatenation condition holds: joining two files produces the sum of their lines. LOC is collected here as logical lines, excluding blank lines and comment lines.

| Metric | Scale Type | Values |
|---|---|---|
| Total Lines of Code (System) | Ratio | 3,067 lines across 19 source files |
| Backend LOC (PHP — 8 files) | Ratio | 290 lines |
| Frontend LOC (JS / HTML / CSS — 11 files) | Ratio | 2,777 lines |
| Largest File (style.css) | Ratio | 1,217 LOC |
| Most Complex File (tracker.js) | Ratio | 186 LOC, 9 functions, CC = 17 |
| Average Backend File Size | Ratio | 36 LOC per file |
| Average Frontend File Size | Ratio | 253 LOC per file |

---

## 2. Complexity Metrics — Cyclomatic Complexity

Cyclomatic Complexity (CC) measures the number of independent execution paths through a module. CC = decision points + 1. It belongs to the **Ratio scale** — the minimum is 1, and CC = 10 is genuinely twice as complex as CC = 5. Higher CC predicts higher testing cost and a greater probability of defects.

| Metric | Scale Type | Values |
|---|---|---|
| signup.php — Highest Backend CC | Ratio | CC = 10 (Moderate risk) |
| login.php | Ratio | CC = 9 (Moderate risk) |
| submitreview.php | Ratio | CC = 6 (Moderate risk) |
| savetracker.php | Ratio | CC = 5 (Low risk) |
| logout.php | Ratio | CC = 3 (Low risk) |
| tracker.js — Highest Frontend CC | Ratio | CC = 17 (File total — 9 functions, each individually CC ≤ 5) |
| review.js | Ratio | CC = 7 (Moderate risk) |
| slideshow.js | Ratio | CC = 4 (Low risk) |
| Risk level: Low (CC 1–5) | Ordinal | Most files and individual functions |
| Risk level: Moderate (CC 6–10) | Ordinal | login.php, signup.php, submitreview.php, review.js |
| Risk level: High (CC 11–20) | Ordinal | tracker.js file total — no single function exceeds 5 |
| Risk level: Very High (CC 21+) | Ordinal | No file in this project reaches this level |

---

## 3. Documentation Metrics — Comment Density

Comment density is an indirect Ratio-scale measure: **(comment lines / total lines) × 100**. Industry best practice recommends 15–25% for production code maintained by a team. Below 10% is considered poor and below 5% is a documentation failure that will create serious maintenance problems as the team grows or original developers leave.

| Metric | Scale Type | Values |
|---|---|---|
| config.php | Ratio | 0% — Critical: no comments on the file that configures the entire database |
| submitreview.php | Ratio | 2% — Very poor: security-sensitive code with almost no explanation |
| slideshow.js | Ratio | 0% — No comments, though the logic is simple enough to follow |
| getreviews.php | Ratio | 5% — Below standard |
| getuserdata.php | Ratio | 9% — Below standard |
| login.php | Ratio | 10% — Borderline acceptable |
| savetracker.php | Ratio | 10% — Borderline acceptable |
| tracker.js | Ratio | 11% — Below standard for its complexity level (CC = 17) |
| signup.php | Ratio | 17% — Good, meets the recommended standard |
| review.js | Ratio | 15% — Good |
| logout.php | Ratio | 21% — Best documented backend file |
| Industry Benchmark | Ratio | 15–25% is considered good practice for team-maintained code |

---

## 4. Coupling Metrics — Module Dependencies

Coupling counts how many external files a module depends on via `require` or `include` statements. It is an **Absolute-scale** measure — a pure count. Low coupling is desirable because a module that depends on nothing can be developed and tested in complete isolation. All JavaScript files in this project achieve zero coupling, which is excellent design.

| Metric | Scale Type | Values |
|---|---|---|
| tracker.js / review.js / slideshow.js | Absolute | 0 dependencies — fully self-contained, ideal for isolated testing |
| logout.php | Absolute | 0 dependencies — destroys session and redirects with no includes |
| getreviews.php | Absolute | 1 dependency — database config only; single focused purpose |
| savetracker.php | Absolute | 1 dependency — database config only; well isolated |
| getuserdata.php | Absolute | 1 dependency — database config only |
| login.php | Absolute | 1 dependency — path is wrong (Defect D4): login is non-functional |
| signup.php | Absolute | 1 dependency — path is wrong (Defect D5): registration is non-functional |
| Average Backend Coupling | Ratio | 0.75 dependencies per file |
| Average Frontend Coupling | Ratio | 0 dependencies per file |

---

## 5. Defect Metrics

Defects were identified through static code inspection — reading source files without executing them. Defect count is on the **Absolute scale**. Defect type is **Nominal** (Coding, Design, Specification — no ordering between categories, so saying one type is worse than another is invalid). Defect severity is **Ordinal** (High > Medium > Low — ordering is valid but computing an average severity is not).

| Metric | Scale Type | Values |
|---|---|---|
| Total Defects Found | Absolute | 7 defects across the codebase |
| High Severity Defects | Ordinal | 4 — D1 (tracker ID), D3 (unclosed tag), D4 (login path), D5 (signup path) |
| Medium Severity Defects | Ordinal | 2 — D2 (localStorage no try/catch), D6 (DB query no try/catch) |
| Low Severity Defects | Ordinal | 1 — D7 (section ID has spaces) |
| Defects by Type: Coding | Nominal | 5 defects — errors in the implementation of logic |
| Defects by Type: Design | Nominal | 2 defects — wrong config file path in login.php and signup.php |
| Defects by Type: Specification | Nominal | 0 defects |
| Backend Defect Density | Ratio | 17.2 defects per 1,000 LOC — high, requires focused testing |
| Frontend Defect Density | Ratio | 0.7 defects per 1,000 LOC — low, acceptable |
| System Defect Density | Ratio | 2.3 defects per 1,000 LOC overall |

---

## 6. Module Risk Profile — Object Profile

The Object Profile combines multiple attributes simultaneously to give a complete picture of each module. A single metric is never sufficient to judge quality. The profile below uses five dimensions: LOC (size), CC (complexity), Comment % (documentation), Coupling (independence), and Defects (correctness).

| Module | LOC | CC | Comment % | Coupling | Defects | Risk Level | Action |
|---|---|---|---|---|---|---|---|
| signup.php | 66 | 10 | 17% | 1 | 1 — D5 | **HIGH** | Fix D5 first |
| login.php | 53 | 9 | 10% | 1 | 1 — D4 | **HIGH** | Fix D4 first |
| tracker.js | 186 | 17 | 11% | 0 | 2 — D1, D2 | **MEDIUM** | Fix D1, D2 |
| index.html | 259 | 1 | 0% | 0 | 2 — D3, D7 | **MEDIUM** | Fix D3, D7 |
| getreviews.php | 16 | 1 | 5% | 1 | 1 — D6 | **MEDIUM** | Fix D6 |
| savetracker.php | 57 | 5 | 10% | 1 | 0 | LOW | Monitor |
| review.js | 56 | 7 | 15% | 0 | 0 | LOW | None needed |
| logout.php | 17 | 3 | 21% | 0 | 0 | LOW | None needed |

---

## 7. Scale Type Validity — Valid and Invalid Statements

A key principle from Chapter 2 is that the scale type determines which statements about measurement data are mathematically valid. The table below tests real statements about this project.

| Statement | Scale Type | Valid? | Reason |
|---|---|---|---|
| tracker.js is 3.5x larger than login.php (186 vs 53 LOC) | Ratio | ✅ Yes | LOC is Ratio — zero exists, ratios are fully valid |
| signup.php is 10x more complex than getreviews.php (CC 10 vs 1) | Ratio | ✅ Yes | CC is Ratio — the ratio 10/1 = 10 is meaningful |
| logout.php is twice as documented as login.php (21% vs 10%) | Ratio | ✅ Yes | Comment density is Ratio — 21/10 = 2.1 is valid |
| D4 is more severe than D7 (High vs Low) | Ordinal | ✅ Yes | Ordinal allows ordering: High > Low is valid |
| The average defect severity is 2.1 | Ordinal | ❌ No | Ordinal gaps are unknown — arithmetic mean is not permitted |
| Coding defects are worse than Design defects | Nominal | ❌ No | Nominal has no ordering — neither type is inherently worse |

---

## Conclusion

These metrics provide a comprehensive view of the Maternal Health Uganda system's code size, structural complexity, documentation coverage, module independence, and defect distribution.

The most urgent finding is that **login.php** and **signup.php** are entirely non-functional due to a wrong database configuration path — correcting these two lines is the single highest-priority action for the development team. All metrics have been collected and interpreted using the correct scale types as defined in Chapter 2, ensuring that every comparison and conclusion in this report is mathematically valid.

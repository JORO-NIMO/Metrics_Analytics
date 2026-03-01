# Applying Software Measurement Theory to the Maternal Health Platform


## Changes by File

---

### 1. `maternal_health.sql` — Database Schema

**Concept applied: Attribute–Scale–Unit model + Dual-scale representation**

#### `emergency_info` table — Dual-Scale Severity

| Before | After |
|--------|-------|
| `severity ENUM('Low','Medium','High')` only | `severity` (ordinal) + `severity_score TINYINT (1–10)` (ratio) |

The original schema used only an **ordinal scale** for severity (Low < Medium < High). Ordinal scales preserve rank order but do not support arithmetic — you cannot say "High is twice as severe as Medium."

A **ratio scale** column (`severity_score`, range 1–10) was added alongside. This follows the PDF's principle that the formal relational system must be capable of supporting meaningful conclusions from the data. Ratio scales allow:
- Sorting with numeric precision
- Averaging across emergency items
- Computing weighted platform scores

Severity mapping used:
| Ordinal | Ratio Score Range |
|---------|-------------------|
| Low     | 1–3               |
| Medium  | 4–6               |
| High    | 7–10              |

#### `services` table — New Attributes

Added `service_category` (**nominal scale** — a label for classification, no ordering) and `view_count` (**ratio scale** — integer count ≥ 0, unit: views). These are **direct measurements** of entities per the measurement activities described in the PDF (problem definition → identify key attributes).

#### `contact_messages` table — Status Attribute

Added `status ENUM('new','in_progress','resolved')` as a **nominal scale** attribute that classifies message handling state without implied ordering.

#### `platform_metrics` table — New Table

A new table was created to hold **direct measurements** of platform usage events:

| Metric | Scale | Unit |
|--------|-------|------|
| `total_page_views` | Ratio | views |
| `emergency_lookups` | Ratio | lookups |
| `service_views` | Ratio | views |
| `total_form_submissions` | Ratio | submissions |

All values start at 0 (true zero — confirming ratio scale validity) and increment monotonically.

#### Added Emergency Data

Three additional emergency entries were seeded, each with a calibrated `severity_score` on the ratio scale, demonstrating the scale's practical use in distinguishing degrees within the same ordinal category (e.g., "Severe Bleeding" = 9, "Prolonged Labour" = 8 — both High, but distinguishable numerically).

---

### 2. `get_emergency.php` — Ordering by Ratio Scale

**Concept applied: Ratio scale supports arithmetic ordering**

```sql
ORDER BY severity_score DESC
```

Previously, no ordering was enforced. Because the ordinal scale alone does not enable numeric sorting, the records came back in insertion order. Now the API sorts by the **ratio scale** `severity_score`, which means the most critical emergencies always appear first — a formally valid operation on a ratio scale (unlike ordinal, which only supports comparison, not subtraction or arithmetic ordering with equal step sizes).

The response now includes both `severity` (ordinal label) and `severity_score` (ratio integer) so consuming code can use whichever scale is appropriate.

---

### 3. `get_services.php` — Direct Measurement Tracking

**Concept applied: Direct measurement — counting service view events**

Each call to `get_services.php` increments the `service_views` ratio-scale counter in `platform_metrics`. This is a **direct measurement** of an attribute of an entity (the platform) — it involves no other attribute or entity, analogous to measuring a program's length in lines of code.

---

### 4. `submit_contact.php` — Measurement Validation

**Concept applied: Measurement validation — detecting conflicts in the empirical model**

The original file had no input validation. The chapter describes **measurement validation** as the process of detecting conflicts between the real-world model and the formal model. In software terms: if a field is required to be a non-empty string (a constraint of the empirical relational system), receiving an empty string is a conflict that must be detected and rejected.

Changes made:
- All three fields (`name`, `phone`, `message`) are now validated for presence.
- `phone` is validated against a pattern (`/^[+0-9\s\-]{7,20}$/`) — enforcing the **nominal scale constraint** that a phone number must conform to an expected format.
- Validation errors are returned as structured JSON with HTTP 422, rather than silently proceeding.
- Successful submissions increment the `total_form_submissions` ratio-scale counter.

---

### 5. `get_metrics.php` — New File: Indirect Measurements

**Concept applied: Indirect measurement — making visible interactions between direct measures**

The PDF distinguishes:
- **Direct measurement**: involves no other attribute or entity (e.g., lines of code)
- **Indirect measurement**: makes visible the interaction between direct measurements (e.g., productivity, software quality)

`get_metrics.php` is a new API endpoint that:

1. Returns all **direct metrics** from `platform_metrics`.
2. Computes **indirect metrics** derived from them:

| Indirect Metric | Formula | What It Measures |
|-----------------|---------|-----------------|
| `emergency_engagement_rate` | `emergency_lookups / total_page_views` | How often users seek emergency info |
| `contact_conversion_rate` | `total_form_submissions / total_page_views` | How often users submit a contact request |

These are ratio-scale results, and a null is returned when the denominator is zero — preserving measurement validity (division by zero is not a defined operation in the ratio scale system).

---

### 6. `main.js` — Dual-Scale Display + Measurement Validation

**Concept applied: Property-oriented measurement display + client-side measurement validation**

#### Dual-Scale Severity Cards

Each emergency card now renders both the ordinal label and the ratio score:

```html
<span class="severity high">High</span>
<span class="severity-score">Score: 9/10</span>
<div class="severity-bar-track">
  <div class="severity-bar-fill high" style="width: 90%"></div>
</div>
```

The severity bar maps the ratio-scale score (1–10) to a proportional bar width (10–100%) — this is an **empirical-to-formal mapping** (real world → number system) as illustrated in the PDF's height measurement example.

#### Client-Side Measurement Validation

Before submitting the contact form, JavaScript now validates all required attributes (matching the server-side validation in `submit_contact.php`). This mirrors the measurement theory concept of verifying the empirical relational system before committing data — catching "invalid entity" conditions (empty required fields) at the earliest possible point.

#### Robust Error Handling

Both `fetch` calls now include `.catch()` blocks that display user-friendly error messages, ensuring the platform degrades gracefully when the formal measurement system (API) is unavailable.

---

### 7. `style.css` — Visual Measurement Representation

**Concept applied: Visual mapping of measurement scales**

New CSS was appended to support the dual-scale severity display:

- `.severity.low / .medium / .high` — colour-coded ordinal labels (green / amber / red)
- `.severity-score` — pill showing numeric ratio score
- `.severity-bar-track / .severity-bar-fill` — proportional bar representing ratio scale visually
- `.service-category` — badge for the nominal-scale service classification label
- Colour coding per severity level is applied to both the label and the bar fill, reinforcing the ordinal ordering visually

---

### 8. `db.php` — Improved Connection Error Handling

The database connection now sets `utf8mb4` charset (ensuring string attributes are stored with full Unicode support) and returns an HTTP 503 status with structured JSON on connection failure — rather than an unformatted die message.

---

## Summary Table

| PDF Concept | Where Applied |
|-------------|---------------|
| **m = ⟨attribute, scale, unit⟩** | All DB columns documented with scale type |
| **Ordinal scale** (Low < Medium < High) | `severity` ENUM maintained + ordered |
| **Ratio scale** (1–10, true zero) | `severity_score` column added |
| **Nominal scale** (classification) | `service_category`, `status` columns |
| **Interval scale** (timestamps) | `created_at` columns |
| **Direct measurement** | `view_count`, `platform_metrics` table |
| **Indirect measurement** | `get_metrics.php` computed ratios |
| **Measurement validation** | `submit_contact.php` input validation |
| **Empirical → formal mapping** | Severity bar (score → bar width %) |
| **Dual-scale representation** | Emergency cards show ordinal + ratio |
| **Ordering by ratio scale** | `get_emergency.php` ORDER BY severity_score |
| **Scale's formal definition** | SQL schema comments document each column's scale |
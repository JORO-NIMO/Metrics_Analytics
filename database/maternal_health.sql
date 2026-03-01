-- ============================================================
-- Maternal Health Database Schema
-- Revised applying Software Measurement Theory (SENG 421 Ch.2)
--
-- Measurement Framework applied:
--   - Each attribute is defined as m = <attribute, scale, unit>
--   - Ordinal scale: severity ENUM (Low < Medium < High)
--   - Ratio scale:   severity_score (1-10, true zero, additive)
--   - Nominal scale: service_category (classification only)
--   - Ratio scale:   view_count (integer counts, unit: views)
--   - Interval scale: timestamps (differences meaningful)
-- ============================================================

CREATE DATABASE IF NOT EXISTS maternal_health;
USE maternal_health;

-- -------------------------------------------------------
-- TABLE: services
-- Entities: maternal health service offerings
-- Key attributes measured:
--   service_name     -> nominal scale (label/identifier)
--   description      -> qualitative (text)
--   service_category -> nominal scale (type classification)
--   view_count       -> ratio scale (integer >= 0, unit: views)
-- -------------------------------------------------------
CREATE TABLE services (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    service_name     VARCHAR(100) NOT NULL,
    description      TEXT         NOT NULL,
    service_category VARCHAR(50)  NOT NULL DEFAULT 'General',
    view_count       INT          NOT NULL DEFAULT 0
);

-- -------------------------------------------------------
-- TABLE: emergency_info
-- Entities: maternal health emergency conditions
-- Key attributes measured:
--   severity       -> ordinal scale: Low < Medium < High
--   severity_score -> ratio scale: 1-10 (enables arithmetic)
--                     Mapping: Low=1-3, Medium=4-6, High=7-10
--
-- Dual-scale approach: same entity mapped to both ordinal
-- and ratio scales per property-oriented measurement theory.
-- Ordinal scale preserves ranking; ratio scale enables
-- quantitative operations (averages, weighted scores).
-- -------------------------------------------------------
CREATE TABLE emergency_info (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    title                VARCHAR(100) NOT NULL,
    short_description    VARCHAR(255) NOT NULL,
    detailed_description TEXT         NOT NULL,
    severity             ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    severity_score       TINYINT      NOT NULL DEFAULT 5,
    advice               TEXT         NOT NULL
);

-- -------------------------------------------------------
-- TABLE: contact_messages
-- Entities: user-submitted support requests
-- Key attributes measured:
--   created_at -> interval scale (timestamp, differences meaningful)
--   status     -> nominal scale (new / in_progress / resolved)
-- -------------------------------------------------------
CREATE TABLE contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    phone      VARCHAR(20)  NOT NULL,
    message    TEXT         NOT NULL,
    status     ENUM('new', 'in_progress', 'resolved') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- TABLE: platform_metrics
-- Purpose: direct measurement of platform usage events.
-- Enables indirect measurements downstream, e.g.:
--   platform_effectiveness = emergency_lookups / page_views
-- All metric_value columns use ratio scale (count >= 0).
-- -------------------------------------------------------
CREATE TABLE platform_metrics (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    metric_name  VARCHAR(100) NOT NULL,
    metric_value INT          NOT NULL DEFAULT 0,
    metric_unit  VARCHAR(50)  NOT NULL DEFAULT 'count',
    recorded_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Initial data
-- -------------------------------------------------------
INSERT INTO services (service_name, description, service_category) VALUES
('Antenatal Care',   'Regular health check-ups during pregnancy',       'Prenatal'),
('Skilled Delivery', 'Childbirth assisted by trained health workers',   'Delivery'),
('Postnatal Care',   'Care for mother and baby after birth',            'Postnatal');

-- severity_score ratio mapping: High=7-10, Medium=4-6, Low=1-3
INSERT INTO emergency_info (title, short_description, detailed_description, severity, severity_score, advice) VALUES
(
    'Severe Bleeding',
    'Excessive bleeding during or after pregnancy',
    'This can be life-threatening. Immediate medical attention is required. Monitor vital signs and do not delay transport.',
    'High', 9,
    'Call emergency services immediately and go to the nearest hospital with obstetric care.'
),
(
    'High Fever',
    'Fever > 38 degrees Celsius during pregnancy',
    'High fever can indicate infection. Monitor temperature and hydration. Seek medical evaluation promptly.',
    'Medium', 5,
    'Visit the nearest health facility. Keep hydrated and rest.'
),
(
    'Prolonged Labour',
    'Labour lasting more than 24 hours without delivery',
    'Prolonged labour increases risk for both mother and baby. Medical intervention may be required.',
    'High', 8,
    'Go to the nearest health facility with maternity services immediately.'
),
(
    'Reduced Fetal Movement',
    'Baby moving less than usual after 28 weeks',
    'A reduction in fetal movement may signal distress. Count movements and seek assessment promptly.',
    'Medium', 6,
    'Contact your midwife or go to the antenatal clinic for a fetal assessment.'
),
(
    'Mild Swelling',
    'Minor swelling of feet and ankles',
    'Mild oedema is common in pregnancy but should be monitored in case it worsens or is accompanied by other symptoms.',
    'Low', 2,
    'Rest, elevate your feet, and report to your next scheduled antenatal visit.'
);

-- Seed platform metrics counters (ratio scale - all start at 0)
INSERT INTO platform_metrics (metric_name, metric_value, metric_unit) VALUES
('total_page_views',       0, 'views'),
('total_form_submissions', 0, 'submissions'),
('emergency_lookups',      0, 'lookups'),
('service_views',          0, 'views');
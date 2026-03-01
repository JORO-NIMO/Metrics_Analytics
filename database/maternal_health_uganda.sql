-- ============================================================
-- Maternal Health Uganda - Complete Database Schema
-- Database: maternal_health_uganda
-- ============================================================

CREATE DATABASE IF NOT EXISTS maternal_health_uganda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maternal_health_uganda;

-- ── USERS TABLE ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    fullname    VARCHAR(150)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    gender      ENUM('M','F','Other') NOT NULL,
    role        ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active   TINYINT(1)          NOT NULL DEFAULT 1,
    last_login  DATETIME            NULL,
    created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ── PREGNANCY TRACKING TABLE ─────────────────────────────────
CREATE TABLE IF NOT EXISTS pregnancy_tracking (
    track_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT          NOT NULL,
    last_period_date  DATE         NOT NULL,
    due_date          DATE         NOT NULL,
    current_week      INT          NOT NULL DEFAULT 0,
    created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ── REVIEWS TABLE ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    review_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    review_text TEXT         NOT NULL,
    rating      TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    is_approved TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ── HEALTH TIPS TABLE ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS health_tips (
    tip_id      INT AUTO_INCREMENT PRIMARY KEY,
    week_number INT          NOT NULL UNIQUE,
    title       VARCHAR(200) NOT NULL,
    content     TEXT         NOT NULL
);

-- ── APPOINTMENTS TABLE ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT          NOT NULL,
    appointment_date DATE         NOT NULL,
    appointment_time TIME         NOT NULL,
    notes            TEXT         NULL,
    status           ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Sample health tips per pregnancy week
INSERT INTO health_tips (week_number, title, content) VALUES
(1,  'Week 1 - Getting Ready',        'Your body is preparing for ovulation. Take folic acid daily (400-800mcg) to prevent neural tube defects.'),
(2,  'Week 2 - Ovulation',            'Ovulation occurs around this time. Stay hydrated and maintain a healthy diet.'),
(3,  'Week 3 - Fertilization',        'The egg may have been fertilized. Avoid alcohol, tobacco, and unnecessary medications.'),
(4,  'Week 4 - Implantation',         'The embryo is implanting. You may notice light spotting. Take a pregnancy test if your period is late.'),
(5,  'Week 5 - Heart Forming',        'Your baby''s heart is beginning to form. Schedule your first prenatal visit now.'),
(6,  'Week 6 - Morning Sickness',     'Morning sickness may begin. Eat small frequent meals. Stay hydrated with water and ginger tea.'),
(7,  'Week 7 - Rapid Growth',         'Baby is growing rapidly. Rest as much as possible and eat iron-rich foods.'),
(8,  'Week 8 - First Ultrasound',     'Consider your first ultrasound this week. You should be able to see the heartbeat.'),
(9,  'Week 9 - Organ Development',    'Major organs are forming. Avoid raw or undercooked foods and unpasteurized products.'),
(10, 'Week 10 - Prenatal Tests',      'Discuss prenatal genetic testing options with your doctor. Continue taking prenatal vitamins.'),
(11, 'Week 11 - Growing Baby',        'Baby''s fingers and toes are fully separated. Increase calcium intake with dairy or leafy greens.'),
(12, 'Week 12 - End of First Trimester', 'You''ve reached the end of the first trimester! Risk of miscarriage significantly decreases.'),
(13, 'Week 13 - Second Trimester Begins', 'Welcome to the second trimester! Energy levels often improve. Stay active with gentle exercise.'),
(14, 'Week 14 - Feeling Better',      'Many women feel much better this week. Consider joining a prenatal yoga or swimming class.'),
(15, 'Week 15 - Feeling Movement',    'You may begin to feel fluttering movements. Schedule your mid-pregnancy ultrasound (anatomy scan).'),
(16, 'Week 16 - Glowing Skin',        'The pregnancy glow is real! Your blood volume has increased. Stay hydrated and moisturize.'),
(17, 'Week 17 - Growing Bump',        'Your bump is more visible now. Sleep on your left side to improve blood flow to baby.'),
(18, 'Week 18 - Anatomy Scan',        'This is a great time for the anatomy scan to check baby''s development and possibly learn the sex.'),
(19, 'Week 19 - Halfway There!',      'You''re nearly halfway! Baby is developing a protective coating called vernix. Eat protein-rich foods.'),
(20, 'Week 20 - Halfway Point',       'Congratulations — you are halfway through your pregnancy! Keep attending prenatal checkups.'),
(21, 'Week 21 - Active Baby',         'Baby is becoming more active. You should feel kicks regularly. Track daily movements.'),
(22, 'Week 22 - Viability',           'Baby is reaching viability. Discuss birth preferences and hospital plans with your midwife.'),
(23, 'Week 23 - Hearing Voices',      'Baby can now hear your voice! Talk, sing, and play music. It helps with bonding.'),
(24, 'Week 24 - Glucose Test',        'Your doctor may recommend a glucose tolerance test this week to check for gestational diabetes.'),
(25, 'Week 25 - Brain Development',   'Baby''s brain is developing rapidly. Eat omega-3 rich foods like fish and nuts.'),
(26, 'Week 26 - Eyes Opening',        'Baby''s eyes are developing and may begin to open. Watch for signs of preeclampsia (headaches, swelling).'),
(27, 'Week 27 - Third Trimester Soon','Almost in the third trimester! Start thinking about your birth plan.'),
(28, 'Week 28 - Third Trimester',     'Welcome to the third trimester! Appointments become more frequent. Monitor baby''s kicks daily.'),
(29, 'Week 29 - Preparing',           'Baby is gaining weight and fat. Prepare your hospital bag. Learn the signs of preterm labor.'),
(30, 'Week 30 - Position',            'Baby may start moving into head-down position. Do pelvic floor exercises daily.'),
(31, 'Week 31 - Lung Development',    'Baby''s lungs are maturing. Avoid heavy lifting and take rest breaks throughout the day.'),
(32, 'Week 32 - Practice Breathing',  'Baby practices breathing movements. Attend prenatal classes to prepare for labor.'),
(33, 'Week 33 - Getting Ready',       'Baby is gaining about 0.5 pounds per week. Prepare your home for baby''s arrival.'),
(34, 'Week 34 - Nearly There',        'Baby''s immune system is strengthening. Finalize your birth plan with your healthcare provider.'),
(35, 'Week 35 - Almost Full Term',    'Baby could arrive any time from week 37. Ensure your hospital bag is packed.'),
(36, 'Week 36 - Final Stretch',       'Baby drops lower into the pelvis. Shortness of breath may ease but bladder pressure increases.'),
(37, 'Week 37 - Full Term',           'Baby is now considered full term! Attend weekly prenatal visits. Watch for labor signs.'),
(38, 'Week 38 - Any Day Now',         'Baby is ready! Signs of labor: regular contractions, water breaking, bloody show. Call your midwife.'),
(39, 'Week 39 - Due Week',            'Your due date is approaching. Stay calm, rest, and contact your healthcare provider with any concerns.'),
(40, 'Week 40 - Due Date',            'Happy due date! Remember: only 5% of babies arrive on their exact due date. Be patient and stay positive.'),
(41, 'Week 41 - Post Term',           'You may be offered an induction. Discuss options with your doctor. Monitor baby''s movements carefully.'),
(42, 'Week 42 - Extended Pregnancy',  'Post-term pregnancy. Your doctor will closely monitor you and baby. Trust your medical team.');

-- Sample admin user (password: Admin@1234)
INSERT INTO users (fullname, email, password, gender, role) VALUES
('Admin User', 'admin@maternalhealthuganda.org',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHwHyfLSu',
 'F', 'admin');

-- Sample approved review
INSERT INTO reviews (user_id, review_text, rating, is_approved) VALUES
(1, 'This platform has been incredibly helpful throughout my pregnancy journey. The weekly tips and tracker are amazing!', 5, 1);

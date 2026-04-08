-- ============================================================
-- Maternal Health Uganda — Complete Database Schema
-- Implements: GQM / GQ(I)M (Chapter 3) + Empirical Investigation (Chapter 4)
-- Run this file ONCE on a fresh MySQL installation
-- ============================================================

CREATE DATABASE IF NOT EXISTS maternal_health_uganda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maternal_health_uganda;

-- ============================================================
-- CORE APPLICATION TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    fullname    VARCHAR(150)          NOT NULL,
    email       VARCHAR(150)          NOT NULL UNIQUE,
    password    VARCHAR(255)          NOT NULL,
    gender      ENUM('M','F','Other') NOT NULL,
    role        ENUM('user','admin')  NOT NULL DEFAULT 'user',
    is_active   TINYINT(1)            NOT NULL DEFAULT 1,
    last_login  DATETIME              NULL,
    created_at  TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pregnancy_tracking (
    track_id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT       NOT NULL,
    last_period_date  DATE      NOT NULL,
    due_date          DATE      NOT NULL,
    current_week      INT       NOT NULL DEFAULT 0,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    review_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT        NOT NULL,
    review_text TEXT       NOT NULL,
    rating      TINYINT    NOT NULL CHECK (rating BETWEEN 1 AND 5),
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    approved_at TIMESTAMP  NULL,
    created_at  TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS health_tips (
    tip_id      INT          AUTO_INCREMENT PRIMARY KEY,
    week_number INT          NOT NULL UNIQUE,
    title       VARCHAR(200) NOT NULL,
    content     TEXT         NOT NULL,
    reviewed_by VARCHAR(150) NULL,
    reviewed_at TIMESTAMP    NULL
);

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT       NOT NULL,
    appointment_date DATE      NOT NULL,
    appointment_time TIME      NOT NULL,
    notes            TEXT      NULL,
    status           ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- GQM / GQ(I)M METRIC TABLES  (Chapter 3)
-- Each table supports one or more Measurement Goals (MG)
-- Formal Measurement Goal template used:
--   Analyze [entity] for the purpose of [purpose]
--   with respect to [quality focus]
--   from the viewpoint of [perspective]
--   in the context of [environment]
-- ============================================================

-- MG1: Analyze tracker calculations for the purpose of evaluating accuracy
--      with respect to error rate per 100 uses,
--      from the viewpoint of the system administrator,
--      in the context of the Maternal Health Uganda PHP/MySQL web app.
CREATE TABLE IF NOT EXISTS tracker_logs (
    log_id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id              INT           NULL,
    last_period_date     DATE          NOT NULL,
    calculated_week      INT           NOT NULL,
    calculated_due_date  DATE          NOT NULL,
    is_error             TINYINT(1)    NOT NULL DEFAULT 0,
    error_reason         VARCHAR(255)  NULL,
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- MG3: Analyze user registration flow for the purpose of improving engagement
--      with respect to signup completion rate,
--      from the viewpoint of the product manager.
CREATE TABLE IF NOT EXISTS page_views (
    view_id     INT AUTO_INCREMENT PRIMARY KEY,
    page        VARCHAR(100) NOT NULL,
    session_id  VARCHAR(255) NULL,
    user_id     INT          NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- MG6: Analyze system reliability for the purpose of reducing failures
--      with respect to error rate per 1000 requests,
--      from the viewpoint of the developer.
CREATE TABLE IF NOT EXISTS system_errors (
    error_id    INT AUTO_INCREMENT PRIMARY KEY,
    endpoint    VARCHAR(200) NOT NULL,
    error_msg   TEXT         NOT NULL,
    severity    ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
    user_id     INT          NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- MG7: Analyze health tip content for the purpose of evaluating accuracy
--      with respect to user-reported inaccuracy rate per month,
--      from the viewpoint of the content administrator.
CREATE TABLE IF NOT EXISTS content_feedback (
    feedback_id   INT AUTO_INCREMENT PRIMARY KEY,
    feedback_type ENUM('tip_error','general') NOT NULL DEFAULT 'general',
    week_number   INT          NULL,
    message       TEXT         NOT NULL,
    user_id       INT          NULL,
    status        ENUM('open','reviewed','resolved') NOT NULL DEFAULT 'open',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at   TIMESTAMP    NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- MG5: Analyze authentication for the purpose of ensuring password security
--      with respect to bcrypt compliance rate,
--      from the viewpoint of the security administrator.
CREATE TABLE IF NOT EXISTS auth_audit_log (
    audit_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NULL,
    event_type  ENUM('login_success','login_fail','signup','password_change') NOT NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ============================================================
-- EMPIRICAL INVESTIGATION TABLES  (Chapter 4)
-- These tables implement the full SE investigation framework:
-- Hypothesis → Variables → Data Collection → Analysis → Results
-- ============================================================

-- Experiment Registry (Ch4: Formal Experiments Planning — 6 phases)
-- Conception → Design → Preparation → Execution → Analysis → Dissemination
CREATE TABLE IF NOT EXISTS experiments (
    experiment_id    INT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(300) NOT NULL,
    -- Phase 1: Conception
    goal             TEXT         NOT NULL,
    -- Phase 2: Design
    hypothesis       TEXT         NOT NULL,
    independent_var  VARCHAR(200) NOT NULL,
    dependent_var    VARCHAR(200) NOT NULL,
    controlled_vars  TEXT         NULL,
    sample_size      INT          NULL,
    investigation_type ENUM('formal_experiment','case_study','survey') NOT NULL DEFAULT 'formal_experiment',
    -- Phase tracking
    phase            ENUM('conception','design','preparation','execution','analysis','dissemination') NOT NULL DEFAULT 'conception',
    started_at       TIMESTAMP    NULL,
    completed_at     TIMESTAMP    NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by       INT          NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Observations / Raw Data (Ch4: Data Collection guidelines DC1-DC4)
-- Supports all 3 contact degrees: First, Second, Third
CREATE TABLE IF NOT EXISTS experiment_observations (
    obs_id          INT AUTO_INCREMENT PRIMARY KEY,
    experiment_id   INT             NOT NULL,
    subject_id      INT             NULL,
    -- DC1: Fully defined measure (entity + attribute + unit + counting rule)
    metric_name     VARCHAR(100)    NOT NULL,
    metric_value    DECIMAL(10,4)   NOT NULL,
    metric_unit     VARCHAR(50)     NOT NULL,
    -- D3: Treatment group assignment
    group_name      VARCHAR(50)     NULL,
    -- Principle 3 (Local Control): blocking group
    block_name      VARCHAR(50)     NULL,
    -- Ch4: 3 degrees of contact
    contact_degree  ENUM('first','second','third') NOT NULL DEFAULT 'second',
    notes           TEXT            NULL,
    observed_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (experiment_id) REFERENCES experiments(experiment_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id)    REFERENCES users(user_id) ON DELETE SET NULL
);

-- Analysis Results (Ch4: Analysis guidelines A1-A5 + Presentation P1-P5)
CREATE TABLE IF NOT EXISTS experiment_results (
    result_id            INT AUTO_INCREMENT PRIMARY KEY,
    experiment_id        INT             NOT NULL,
    metric_name          VARCHAR(100)    NOT NULL,
    group_name           VARCHAR(50)     NOT NULL,
    -- P4: Descriptive statistics
    n_observations       INT             NOT NULL DEFAULT 0,
    mean_value           DECIMAL(10,4)   NULL,
    std_deviation        DECIMAL(10,4)   NULL,
    min_value            DECIMAL(10,4)   NULL,
    max_value            DECIMAL(10,4)   NULL,
    -- P2: Statistical significance (A1: multiple testing control)
    p_value              DECIMAL(8,6)    NULL,
    is_significant       TINYINT(1)      NULL,
    -- I2: Practical importance vs statistical significance
    effect_size          DECIMAL(8,4)    NULL,
    practical_note       TEXT            NULL,
    -- I1, I3: Conclusions and limitations
    hypothesis_supported TINYINT(1)      NULL,
    conclusion           TEXT            NULL,
    limitations          TEXT            NULL,
    computed_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (experiment_id) REFERENCES experiments(experiment_id) ON DELETE CASCADE
);

-- Survey Investigation (Ch4: Survey = retrospective, investigate in the large)
CREATE TABLE IF NOT EXISTS survey_responses (
    response_id         INT AUTO_INCREMENT PRIMARY KEY,
    survey_name         VARCHAR(150) NOT NULL,
    user_id             INT          NULL,
    q_tracker_useful    TINYINT      NULL CHECK (q_tracker_useful BETWEEN 1 AND 5),
    q_tips_accurate     TINYINT      NULL CHECK (q_tips_accurate BETWEEN 1 AND 5),
    q_site_easy_to_use  TINYINT      NULL CHECK (q_site_easy_to_use BETWEEN 1 AND 5),
    q_would_recommend   TINYINT      NULL CHECK (q_would_recommend BETWEEN 1 AND 5),
    q_overall_satisfy   TINYINT      NULL CHECK (q_overall_satisfy BETWEEN 1 AND 5),
    open_comment        TEXT         NULL,
    submitted_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Case Study Events (Ch4: Case study = research in the typical)
CREATE TABLE IF NOT EXISTS case_study_events (
    event_id        INT AUTO_INCREMENT PRIMARY KEY,
    event_category  ENUM('feature_use','error_encounter','user_feedback','admin_action','system_event') NOT NULL,
    event_name      VARCHAR(150) NOT NULL,
    description     TEXT         NULL,
    user_id         INT          NULL,
    impact_level    ENUM('low','medium','high') NOT NULL DEFAULT 'low',
    related_metric  VARCHAR(20)  NULL,
    occurred_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- ============================================================
-- GQM INDICATOR VIEWS  (Step 6 of GQ(I)M — visual displays)
-- ============================================================

CREATE OR REPLACE VIEW v_tracker_accuracy AS
SELECT
    COUNT(*)                                                     AS total_uses,
    SUM(is_error)                                                AS error_count,
    ROUND(SUM(is_error)/NULLIF(COUNT(*),0)*100, 2)              AS error_rate_pct
FROM tracker_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

CREATE OR REPLACE VIEW v_tips_coverage AS
SELECT
    COUNT(*)           AS total_tips,
    COUNT(reviewed_by) AS reviewed_tips,
    ROUND(COUNT(reviewed_by)/42*100,1) AS reviewed_pct
FROM health_tips;

CREATE OR REPLACE VIEW v_registration_funnel AS
SELECT
    (SELECT COUNT(*) FROM page_views WHERE page='signup')    AS signup_page_visits,
    (SELECT COUNT(*) FROM users WHERE role='user')           AS registered_users,
    ROUND(
        (SELECT COUNT(*) FROM users WHERE role='user') /
        NULLIF((SELECT COUNT(*) FROM page_views WHERE page='signup'),0)*100
    ,1) AS completion_rate_pct;

CREATE OR REPLACE VIEW v_return_visits AS
SELECT
    COUNT(*) AS total_users,
    SUM(CASE WHEN DATEDIFF(NOW(),last_login)<=7  THEN 1 ELSE 0 END) AS active_last_7_days,
    SUM(CASE WHEN DATEDIFF(NOW(),last_login)<=30 THEN 1 ELSE 0 END) AS active_last_30_days,
    ROUND(SUM(CASE WHEN DATEDIFF(NOW(),last_login)<=7 THEN 1 ELSE 0 END)/NULLIF(COUNT(*),0)*100,1) AS weekly_active_pct
FROM users WHERE is_active=1 AND last_login IS NOT NULL;

CREATE OR REPLACE VIEW v_password_security AS
SELECT
    COUNT(*) AS total_users,
    SUM(CASE WHEN password LIKE '$2y$%' THEN 1 ELSE 0 END) AS bcrypt_count,
    ROUND(SUM(CASE WHEN password LIKE '$2y$%' THEN 1 ELSE 0 END)/NULLIF(COUNT(*),0)*100,1) AS compliance_pct
FROM users;

CREATE OR REPLACE VIEW v_system_errors AS
SELECT endpoint, severity, COUNT(*) AS error_count, DATE(created_at) AS error_date
FROM system_errors WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY endpoint, severity, DATE(created_at) ORDER BY error_date DESC;

CREATE OR REPLACE VIEW v_review_moderation AS
SELECT
    COUNT(*) AS total_reviews,
    SUM(is_approved) AS approved_reviews,
    ROUND(SUM(is_approved)/NULLIF(COUNT(*),0)*100,1) AS approval_rate_pct,
    ROUND(AVG(TIMESTAMPDIFF(HOUR,created_at,approved_at)),1) AS avg_hours_to_approve
FROM reviews WHERE approved_at IS NOT NULL;

CREATE OR REPLACE VIEW v_content_feedback AS
SELECT
    COUNT(*) AS total_reports,
    SUM(CASE WHEN status='open'     THEN 1 ELSE 0 END) AS open_reports,
    SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) AS resolved_reports,
    SUM(CASE WHEN feedback_type='tip_error' THEN 1 ELSE 0 END) AS tip_error_reports
FROM content_feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Experiment summary view
CREATE OR REPLACE VIEW v_experiment_summary AS
SELECT e.experiment_id, e.title, e.hypothesis, e.independent_var, e.dependent_var,
       e.investigation_type, e.phase,
       COUNT(DISTINCT o.obs_id)     AS total_observations,
       COUNT(DISTINCT o.subject_id) AS total_subjects,
       e.started_at, e.completed_at
FROM experiments e
LEFT JOIN experiment_observations o ON e.experiment_id=o.experiment_id
GROUP BY e.experiment_id;

-- Survey aggregated view
CREATE OR REPLACE VIEW v_survey_results AS
SELECT survey_name, COUNT(*) AS total_responses,
       ROUND(AVG(q_tracker_useful),2)   AS avg_tracker_useful,
       ROUND(AVG(q_tips_accurate),2)    AS avg_tips_accurate,
       ROUND(AVG(q_site_easy_to_use),2) AS avg_site_easy_to_use,
       ROUND(AVG(q_would_recommend),2)  AS avg_would_recommend,
       ROUND(AVG(q_overall_satisfy),2)  AS avg_overall_satisfy
FROM survey_responses GROUP BY survey_name;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

INSERT INTO health_tips (week_number, title, content) VALUES
(1,'Week 1 - Getting Ready','Your body is preparing for ovulation. Take folic acid daily (400-800mcg) to prevent neural tube defects.'),
(2,'Week 2 - Ovulation','Ovulation occurs around this time. Stay hydrated and maintain a healthy diet.'),
(3,'Week 3 - Fertilization','The egg may have been fertilized. Avoid alcohol, tobacco, and unnecessary medications.'),
(4,'Week 4 - Implantation','The embryo is implanting. You may notice light spotting. Take a pregnancy test if your period is late.'),
(5,'Week 5 - Heart Forming','Your baby''s heart is beginning to form. Schedule your first prenatal visit now.'),
(6,'Week 6 - Morning Sickness','Morning sickness may begin. Eat small frequent meals. Stay hydrated with water and ginger tea.'),
(7,'Week 7 - Rapid Growth','Baby is growing rapidly. Rest as much as possible and eat iron-rich foods.'),
(8,'Week 8 - First Ultrasound','Consider your first ultrasound this week. You should be able to see the heartbeat.'),
(9,'Week 9 - Organ Development','Major organs are forming. Avoid raw or undercooked foods and unpasteurized products.'),
(10,'Week 10 - Prenatal Tests','Discuss prenatal genetic testing options with your doctor. Continue taking prenatal vitamins.'),
(11,'Week 11 - Growing Baby','Baby''s fingers and toes are fully separated. Increase calcium intake with dairy or leafy greens.'),
(12,'Week 12 - End of First Trimester','You''ve reached the end of the first trimester! Risk of miscarriage significantly decreases.'),
(13,'Week 13 - Second Trimester','Welcome to the second trimester! Energy levels often improve. Stay active with gentle exercise.'),
(14,'Week 14 - Feeling Better','Many women feel much better this week. Consider joining a prenatal yoga or swimming class.'),
(15,'Week 15 - Feeling Movement','You may begin to feel fluttering movements. Schedule your mid-pregnancy anatomy scan.'),
(16,'Week 16 - Glowing Skin','The pregnancy glow is real! Your blood volume has increased. Stay hydrated and moisturize.'),
(17,'Week 17 - Growing Bump','Your bump is more visible now. Sleep on your left side to improve blood flow to baby.'),
(18,'Week 18 - Anatomy Scan','This is a great time for the anatomy scan to check baby''s development.'),
(19,'Week 19 - Halfway There!','You''re nearly halfway! Baby is developing a protective coating called vernix. Eat protein-rich foods.'),
(20,'Week 20 - Halfway Point','Congratulations — you are halfway through your pregnancy! Keep attending prenatal checkups.'),
(21,'Week 21 - Active Baby','Baby is becoming more active. You should feel kicks regularly. Track daily movements.'),
(22,'Week 22 - Viability','Baby is reaching viability. Discuss birth preferences and hospital plans with your midwife.'),
(23,'Week 23 - Hearing Voices','Baby can now hear your voice! Talk, sing, and play music. It helps with bonding.'),
(24,'Week 24 - Glucose Test','Your doctor may recommend a glucose tolerance test this week to screen for gestational diabetes.'),
(25,'Week 25 - Brain Development','Baby''s brain is developing rapidly. Eat omega-3 rich foods like fish and nuts.'),
(26,'Week 26 - Eyes Opening','Baby''s eyes are developing. Watch for signs of preeclampsia: headaches and swelling.'),
(27,'Week 27 - Third Trimester Soon','Almost in the third trimester! Start thinking about your birth plan.'),
(28,'Week 28 - Third Trimester','Welcome to the third trimester! Appointments become more frequent. Monitor baby''s kicks daily.'),
(29,'Week 29 - Preparing','Baby is gaining weight and fat. Prepare your hospital bag. Learn signs of preterm labor.'),
(30,'Week 30 - Position','Baby may start moving into head-down position. Do pelvic floor exercises daily.'),
(31,'Week 31 - Lung Development','Baby''s lungs are maturing. Avoid heavy lifting and take rest breaks throughout the day.'),
(32,'Week 32 - Practice Breathing','Baby practices breathing movements. Attend prenatal classes to prepare for labor.'),
(33,'Week 33 - Getting Ready','Baby is gaining about 0.5 pounds per week. Prepare your home for baby''s arrival.'),
(34,'Week 34 - Nearly There','Baby''s immune system is strengthening. Finalize your birth plan with your healthcare provider.'),
(35,'Week 35 - Almost Full Term','Baby could arrive any time from week 37. Ensure your hospital bag is packed.'),
(36,'Week 36 - Final Stretch','Baby drops lower into the pelvis. Shortness of breath may ease but bladder pressure increases.'),
(37,'Week 37 - Full Term','Baby is now full term! Attend weekly prenatal visits. Watch for labor signs.'),
(38,'Week 38 - Any Day Now','Baby is ready! Signs of labor: regular contractions, water breaking, bloody show.'),
(39,'Week 39 - Due Week','Your due date is approaching. Stay calm, rest, and contact your provider with any concerns.'),
(40,'Week 40 - Due Date','Happy due date! Only 5% of babies arrive on their exact due date. Be patient and positive.'),
(41,'Week 41 - Post Term','You may be offered an induction. Discuss options with your doctor. Monitor movements carefully.'),
(42,'Week 42 - Extended Pregnancy','Post-term pregnancy. Your doctor will closely monitor you and baby. Trust your medical team.');

-- Admin user (password: Admin@1234)
INSERT INTO users (fullname, email, password, gender, role) VALUES
('Admin User','admin@maternalhealthuganda.org',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHwHyfLSu','F','admin');

-- Sample approved review
INSERT INTO reviews (user_id, review_text, rating, is_approved, approved_at) VALUES
(1,'This platform has been incredibly helpful throughout my pregnancy journey!',5,1,NOW());

-- Seed experiment
INSERT INTO experiments (title, goal, hypothesis, independent_var, dependent_var, controlled_vars, sample_size, investigation_type, phase, started_at, created_by) VALUES
(
  'Effect of Input Validation on Tracker Accuracy',
  'Determine whether strict input validation in savetracker.php reduces calculation errors.',
  'Adding input validation will reduce tracker error rate by >50% within 30 days compared to pre-validation baseline.',
  'Input validation (absent vs present)',
  'Tracker error rate (errors per 100 uses)',
  'User experience; device type; internet speed; time of year',
  100, 'formal_experiment', 'execution', NOW(), 1
);

-- Sample survey responses
INSERT INTO survey_responses (survey_name, q_tracker_useful, q_tips_accurate, q_site_easy_to_use, q_would_recommend, q_overall_satisfy, open_comment) VALUES
('Q1-2026 Baseline',4,4,5,5,4,'Very easy to use and the tips are helpful'),
('Q1-2026 Baseline',5,5,4,5,5,'Wonderful resource for Ugandan mothers'),
('Q1-2026 Baseline',3,4,4,4,4,'Sometimes slow but content is great'),
('Q1-2026 Baseline',5,3,5,4,4,'Tips could be more specific to Uganda context'),
('Q1-2026 Baseline',4,5,5,5,5,'I recommended this to all pregnant friends');

-- Sample case study events
INSERT INTO case_study_events (event_category, event_name, description, impact_level, related_metric) VALUES
('feature_use','Tracker Used Successfully','User calculated due date and received week 24 health tip','medium','M1.1'),
('error_encounter','Future Date Entered','User entered a future LMP date — validation flagged correctly','medium','M1.2'),
('user_feedback','Tip Inaccuracy Reported','Week 6 tip missing mention of safe antiemetics','high','M7.2'),
('admin_action','Health Tip Reviewed','Admin reviewed and updated week 12 tip for accuracy','low','M2.1'),
('system_event','Peak Traffic — No Errors','50 concurrent users, zero errors recorded','low','M6.2');

-- ============================================================
-- LECTURE 05: SOFTWARE SIZE METRICS
-- ============================================================

-- 1. LOC Measurements (Length dimension)
CREATE TABLE IF NOT EXISTS loc_measurements (
    loc_id          INT AUTO_INCREMENT PRIMARY KEY,
    file_name       VARCHAR(100)  NOT NULL,
    file_path       VARCHAR(255)  NOT NULL,
    total_loc       INT           NOT NULL DEFAULT 0,
    ncloc           INT           NOT NULL DEFAULT 0  COMMENT 'Non-commented source lines (working code)',
    cloc            INT           NOT NULL DEFAULT 0  COMMENT 'Commented lines',
    blank_lines     INT           NOT NULL DEFAULT 0,
    comment_density DECIMAL(5,2)  NOT NULL DEFAULT 0.00 COMMENT 'CLOC/LOC x 100',
    measured_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Halstead Metrics (Length dimension — token-based)
CREATE TABLE IF NOT EXISTS halstead_metrics (
    halstead_id      INT AUTO_INCREMENT PRIMARY KEY,
    file_name        VARCHAR(100)  NOT NULL,
    mu1              INT           NOT NULL COMMENT 'Distinct operators',
    mu2              INT           NOT NULL COMMENT 'Distinct operands',
    n1               INT           NOT NULL COMMENT 'Total operator occurrences',
    n2               INT           NOT NULL COMMENT 'Total operand occurrences',
    vocabulary       INT           NOT NULL COMMENT 'mu1 + mu2',
    length_n         INT           NOT NULL COMMENT 'N1 + N2',
    estimated_length DECIMAL(10,4) NOT NULL COMMENT 'mu1*log2(mu1) + mu2*log2(mu2)',
    volume           DECIMAL(10,4) NOT NULL COMMENT 'N * log2(vocabulary)',
    difficulty       DECIMAL(10,4) NOT NULL COMMENT '(mu1/2) * (N2/mu2)',
    effort           DECIMAL(12,4) NOT NULL COMMENT 'Volume * Difficulty',
    measured_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Function Points (Functionality dimension)
CREATE TABLE IF NOT EXISTS function_points (
    fp_id           INT AUTO_INCREMENT PRIMARY KEY,
    component_type  ENUM('EI','EO','EQ','ILF','EIF') NOT NULL,
    component_name  VARCHAR(150)  NOT NULL,
    description     VARCHAR(255)  NOT NULL,
    count_value     INT           NOT NULL DEFAULT 0,
    weight          INT           NOT NULL,
    weighted_value  INT           NOT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. Reuse Metrics (Reuse dimension)
CREATE TABLE IF NOT EXISTS reuse_metrics (
    reuse_id                  INT AUTO_INCREMENT PRIMARY KEY,
    file_name                 VARCHAR(100) NOT NULL,
    total_loc                 INT          NOT NULL,
    verbatim_loc              INT          NOT NULL DEFAULT 0 COMMENT 'Copied without changes',
    slightly_modified_loc     INT          NOT NULL DEFAULT 0 COMMENT 'Under 25% changed',
    extensively_modified_loc  INT          NOT NULL DEFAULT 0 COMMENT '25% or more changed',
    new_loc                   INT          NOT NULL DEFAULT 0 COMMENT 'Written from scratch',
    reuse_level               DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '% of items from reuse repository',
    reuse_density             DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Normalised reuse count',
    created_at                TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- LECTURE 06: STRUCTURAL COMPLEXITY
-- ============================================================

-- 5. Cyclomatic Complexity (Control-flow: v(G) = 1 + d)
CREATE TABLE IF NOT EXISTS cyclomatic_complexity (
    cc_id           INT AUTO_INCREMENT PRIMARY KEY,
    file_name       VARCHAR(100) NOT NULL,
    decision_points INT          NOT NULL DEFAULT 0 COMMENT 'Count of if/while/for/foreach/case/catch',
    cyclomatic_v    INT          NOT NULL DEFAULT 0 COMMENT 'v(G) = 1 + decision_points',
    risk_level      ENUM('low','moderate','high','very_high') NOT NULL DEFAULT 'low'
                    COMMENT 'low=1-10, moderate=11-20, high=21-50, very_high=51+',
    measured_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 6. Cohesion per module (CH(C) = internal / (internal + external))
CREATE TABLE IF NOT EXISTS cohesion_metrics (
    cohesion_id         INT AUTO_INCREMENT PRIMARY KEY,
    module_name         VARCHAR(100) NOT NULL,
    module_description  VARCHAR(255) NOT NULL,
    internal_relations  INT          NOT NULL DEFAULT 0,
    external_relations  INT          NOT NULL DEFAULT 0,
    cohesion_ratio      DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    cohesion_pct        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    cohesion_type       ENUM('functional','sequential','communicative','procedural','temporal','logical','coincidental')
                        NOT NULL DEFAULT 'functional',
    measured_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 7. Coupling between module pairs (c(x,y) = type_rank + n/(n+1))
CREATE TABLE IF NOT EXISTS coupling_metrics (
    coupling_id     INT AUTO_INCREMENT PRIMARY KEY,
    module_x        VARCHAR(100) NOT NULL,
    module_y        VARCHAR(100) NOT NULL,
    coupling_type   ENUM('R0','R1','R2','R3','R4') NOT NULL DEFAULT 'R1'
                    COMMENT 'R0=none,R1=data,R2=stamp,R3=control,R4=content',
    coupling_rank   INT          NOT NULL COMMENT 'R0=0 R1=1 R2=2 R3=3 R4=4',
    interconnections INT         NOT NULL DEFAULT 1,
    coupling_value  DECIMAL(8,4) NOT NULL COMMENT 'coupling_rank + n/(n+1)',
    measured_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 8. Information Flow — Fan-in / Fan-out per module
CREATE TABLE IF NOT EXISTS information_flow (
    flow_id      INT AUTO_INCREMENT PRIMARY KEY,
    module_name  VARCHAR(100)  NOT NULL,
    fan_in       INT           NOT NULL DEFAULT 0,
    fan_out      INT           NOT NULL DEFAULT 0,
    ifc_value    DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT '(fan_in * fan_out)^2',
    measured_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 9. Architecture Morphology
CREATE TABLE IF NOT EXISTS architecture_metrics (
    arch_id        INT AUTO_INCREMENT PRIMARY KEY,
    system_name    VARCHAR(100) NOT NULL DEFAULT 'Maternal Health Uganda',
    nodes          INT          NOT NULL COMMENT 'Number of backend modules',
    edges          INT          NOT NULL COMMENT 'Number of module-to-module relationships',
    arch_depth     INT          NOT NULL COMMENT 'Longest path root to leaf',
    arch_width     INT          NOT NULL COMMENT 'Max nodes at any single layer',
    edge_node_ratio DECIMAL(6,4) NOT NULL COMMENT 'edges / nodes',
    impurity       DECIMAL(8,6) NOT NULL COMMENT 'm(G) = 2*(e-n+1) / ((n-1)*(n-2))',
    measured_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 10. Data Structure Complexity per file
CREATE TABLE IF NOT EXISTS data_structure_complexity (
    dsc_id          INT AUTO_INCREMENT PRIMARY KEY,
    file_name       VARCHAR(100) NOT NULL,
    integer_vars    INT          NOT NULL DEFAULT 0 COMMENT 'ni — count of integer variables',
    string_vars     INT          NOT NULL DEFAULT 0 COMMENT 'ns — count of string variables',
    array_vars      INT          NOT NULL DEFAULT 0 COMMENT 'na — count of array variables',
    avg_array_size  INT          NOT NULL DEFAULT 0,
    c1_integers     INT          NOT NULL DEFAULT 0 COMMENT 'ni * 1',
    c2_strings      INT          NOT NULL DEFAULT 0 COMMENT 'ns * 2',
    c3_arrays       INT          NOT NULL DEFAULT 0 COMMENT 'na * 2 * avg_array_size',
    total_complexity INT         NOT NULL DEFAULT 0 COMMENT 'C1 + C2 + C3',
    measured_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SEED DATA: LECTURE 05
-- ============================================================

-- LOC (measured from actual source files)
INSERT INTO loc_measurements (file_name, file_path, total_loc, ncloc, cloc, blank_lines, comment_density) VALUES
('metrics_logger.php', 'backend/metrics_logger.php', 495, 417, 35, 43, 7.07),
('savetracker.php',    'backend/savetracker.php',     97,  80,  0, 17, 0.00),
('signup.php',         'backend/signup.php',           57,  47,  0, 10, 0.00),
('login.php',          'backend/login.php',            59,  49,  0, 10, 0.00),
('getexperiments.php', 'backend/getexperiments.php',   54,  41,  4,  9, 7.41),
('submitsurvey.php',   'backend/submitsurvey.php',     46,  39,  1,  6, 2.17),
('computeresults.php', 'backend/computeresults.php',   42,  35,  0,  7, 0.00),
('submitfeedback.php', 'backend/submitfeedback.php',   42,  31,  3,  8, 7.14),
('submitreview.php',   'backend/submitreview.php',     40,  32,  0,  8, 0.00),
('getuserdata.php',    'backend/getuserdata.php',      38,  27,  4,  7,10.53),
('config.php',         'backend/config.php',           30,  26,  2,  2, 6.67),
('getmetrics.php',     'backend/getmetrics.php',       27,  24,  0,  3, 0.00),
('logpageview.php',    'backend/logpageview.php',      26,  20,  2,  4, 7.69),
('getreviews.php',     'backend/getreviews.php',       22,  17,  1,  4, 4.55),
('logout.php',         'backend/logout.php',            7,   7,  0,  0, 0.00);

-- Halstead (key files measured; operators = PHP keywords+symbols, operands = vars+literals)
INSERT INTO halstead_metrics (file_name, mu1, mu2, n1, n2, vocabulary, length_n, estimated_length, volume, difficulty, effort) VALUES
('login.php',          18, 12, 42, 28, 30,  70, 109.37,  341.96, 21.00,   7181.22),
('signup.php',         16, 14, 38, 32, 30,  70, 105.73,  331.42, 16.71,   5538.03),
('savetracker.php',    22, 18, 68, 45, 40, 113, 181.42,  601.52, 27.50,  16541.80),
('metrics_logger.php', 28, 35,210,180, 63, 390, 481.22, 2341.74, 54.00, 126453.96);

-- Function Points (EI=4, EO=5, EQ=4, ILF=10, EIF=7)
INSERT INTO function_points (component_type, component_name, description, count_value, weight, weighted_value) VALUES
('EI','User Registration',        'Mother submits name, email, password, gender to create account',1,4, 4),
('EI','User Login',               'Mother submits email and password to authenticate',             1,4, 4),
('EI','Pregnancy Tracker Input',  'Mother submits LMP date and cycle length',                     1,4, 4),
('EI','Review Submission',        'Mother submits star rating and comment',                        1,4, 4),
('EI','Content Feedback',         'Mother reports inaccurate health tip',                          1,4, 4),
('EI','Survey Submission',        'Mother submits 5-question satisfaction survey',                 1,4, 4),
('EO','Pregnancy Week Result',    'System outputs current week, trimester, due date',              1,5, 5),
('EO','Health Tip Display',       'System outputs week-specific health tip',                       1,5, 5),
('EO','GQM Metrics Dashboard',    'Admin sees all 8 quality indicators',                           1,5, 5),
('EO','Experiment Results',       'System outputs mean, std dev, min, max per group',              1,5, 5),
('EO','Review List Output',       'System outputs approved community reviews',                     1,5, 5),
('EQ','Get User Profile',         'Retrieve logged-in mother profile',                             1,4, 4),
('EQ','Get Health Tips List',     'Retrieve all 42 health tips for admin review',                  1,4, 4),
('EQ','Get Survey Results',       'Retrieve aggregated survey statistics',                         1,4, 4),
('EQ','Get Case Study Events',    'Retrieve historical user activity events',                      1,4, 4),
('EQ','Get Experiments',          'Retrieve all experiments and their status',                     1,4, 4),
('ILF','Users Table',             'Registered mothers and admin accounts',                         1,10,10),
('ILF','Pregnancy Tracking',      'Tracker results per user per session',                          1,10,10),
('ILF','Health Tips',             '42 curated weekly health tips',                                 1,10,10),
('ILF','Reviews',                 'Community reviews pending and approved',                        1,10,10),
('ILF','Survey Responses',        'All satisfaction survey submissions',                           1,10,10),
('ILF','Case Study Events',       'All automatically logged user events',                          1,10,10),
('EIF','None',                    'No external system interfaces in this project',                 0,7,  0);

-- Reuse Metrics
INSERT INTO reuse_metrics (file_name, total_loc, verbatim_loc, slightly_modified_loc, extensively_modified_loc, new_loc, reuse_level, reuse_density) VALUES
('config.php',         30, 25,  0,  0,  5, 83.33, 0.83),
('login.php',          59,  0, 10,  0, 49, 16.95, 0.17),
('signup.php',         57,  0, 10,  0, 47, 17.54, 0.18),
('logpageview.php',    26,  0,  0,  5, 21, 19.23, 0.19),
('savetracker.php',    97,  0,  0, 10, 87, 10.31, 0.10),
('metrics_logger.php',495,  0,  0, 40,455,  8.08, 0.08);

-- ============================================================
-- SEED DATA: LECTURE 06
-- ============================================================

-- Cyclomatic Complexity (measured from source)
INSERT INTO cyclomatic_complexity (file_name, decision_points, cyclomatic_v, risk_level) VALUES
('metrics_logger.php', 17, 18, 'moderate'),
('signup.php',          9, 10, 'low'),
('savetracker.php',     8,  9, 'low'),
('computeresults.php',  4,  5, 'low'),
('login.php',           4,  5, 'low'),
('submitfeedback.php',  5,  6, 'low'),
('submitreview.php',    4,  5, 'low'),
('submitsurvey.php',    4,  5, 'low'),
('getexperiments.php',  2,  3, 'low'),
('getmetrics.php',      2,  3, 'low'),
('config.php',          1,  2, 'low'),
('getuserdata.php',     1,  2, 'low'),
('logpageview.php',     2,  3, 'low'),
('getreviews.php',      0,  1, 'low'),
('logout.php',          0,  1, 'low');

-- Cohesion (CH(C) = internal / (internal + external))
INSERT INTO cohesion_metrics (module_name, module_description, internal_relations, external_relations, cohesion_ratio, cohesion_pct, cohesion_type) VALUES
('Authentication',     'login.php + signup.php + logout.php — user identity management',            6, 2, 0.7500, 75.00, 'functional'),
('Tracker',            'savetracker.php — pregnancy week calculation and session logging',            8, 3, 0.7273, 72.73, 'functional'),
('Metrics Logger',     'metrics_logger.php — central logging for GQM and investigation data',       18, 8, 0.6923, 69.23, 'communicative'),
('Survey',             'submitsurvey.php — satisfaction survey collection',                          4, 2, 0.6667, 66.67, 'sequential'),
('Admin APIs',         'getmetrics.php + getexperiments.php + computeresults.php',                  10, 5, 0.6667, 66.67, 'communicative'),
('Content',            'submitreview.php + submitfeedback.php + getreviews.php',                     6, 4, 0.6000, 60.00, 'sequential'),
('Config',             'config.php — database connection (single responsibility)',                   2, 0, 1.0000,100.00, 'functional');

-- Coupling (c(x,y) = coupling_rank + n/(n+1))
INSERT INTO coupling_metrics (module_x, module_y, coupling_type, coupling_rank, interconnections, coupling_value) VALUES
('config.php',         'login.php',          'R1',1,1,1.5000),
('config.php',         'signup.php',         'R1',1,1,1.5000),
('config.php',         'savetracker.php',    'R1',1,1,1.5000),
('config.php',         'metrics_logger.php', 'R1',1,1,1.5000),
('metrics_logger.php', 'login.php',          'R1',1,2,1.6667),
('metrics_logger.php', 'signup.php',         'R1',1,2,1.6667),
('metrics_logger.php', 'savetracker.php',    'R2',2,3,2.7500),
('metrics_logger.php', 'submitsurvey.php',   'R1',1,2,1.6667),
('metrics_logger.php', 'submitfeedback.php', 'R1',1,2,1.6667),
('savetracker.php',    'computeresults.php', 'R2',2,2,2.6667),
('getmetrics.php',     'metrics_logger.php', 'R1',1,3,1.7500),
('getexperiments.php', 'metrics_logger.php', 'R1',1,2,1.6667);

-- Information Flow (IFC = (fan_in * fan_out)^2)
INSERT INTO information_flow (module_name, fan_in, fan_out, ifc_value) VALUES
('metrics_logger.php',  7, 6, 1764.0000),
('savetracker.php',     3, 4,  144.0000),
('getmetrics.php',      4, 1,   16.0000),
('login.php',           2, 3,   36.0000),
('signup.php',          2, 3,   36.0000),
('getexperiments.php',  3, 1,    9.0000),
('submitsurvey.php',    2, 2,   16.0000),
('submitfeedback.php',  2, 2,   16.0000),
('computeresults.php',  2, 1,    4.0000),
('logpageview.php',     2, 2,   16.0000),
('config.php',          0, 8,    0.0000);

-- Architecture (15 nodes=PHP files, 28 edges=relationships, depth=3 layers, width=8 at endpoint layer)
INSERT INTO architecture_metrics (system_name, nodes, edges, arch_depth, arch_width, edge_node_ratio, impurity) VALUES
('Maternal Health Uganda', 15, 28, 3, 8, 1.8667, 0.142857);

-- Data Structure Complexity (C = ni*1 + ns*2 + na*2*size)
INSERT INTO data_structure_complexity (file_name, integer_vars, string_vars, array_vars, avg_array_size, c1_integers, c2_strings, c3_arrays, total_complexity) VALUES
('login.php',          3,  4, 0,  0,  3,  8,  0,  11),
('signup.php',         2,  5, 0,  0,  2, 10,  0,  12),
('savetracker.php',    6,  3, 2,  4,  6,  6, 16,  28),
('metrics_logger.php', 8, 12, 3,  6,  8, 24, 36,  68),
('getmetrics.php',     2,  2, 2, 10,  2,  4, 40,  46),
('computeresults.php', 5,  2, 3,  5,  5,  4, 30,  39);

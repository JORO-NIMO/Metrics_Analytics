<?php
/**
 * MetricsLogger.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Implements both:
 *   Chapter 3: GQM / GQ(I)M — Goal-Based Measurement (10-step GQIM process)
 *   Chapter 4: Empirical Investigation — Formal Experiments, Case Studies, Surveys
 *
 * GQ(I)M Measurement Goals implemented:
 *   MG1 — Tracker accuracy       (entity: tracker, attribute: error rate)
 *   MG2 — Health tip coverage    (entity: health_tips, attribute: review %)
 *   MG3 — Registration funnel    (entity: signup flow, attribute: completion %)
 *   MG4 — User return visits     (entity: users, attribute: weekly active %)
 *   MG5 — Password security      (entity: users, attribute: bcrypt compliance %)
 *   MG6 — System reliability     (entity: endpoints, attribute: error rate)
 *   MG7 — Content inaccuracies   (entity: tips, attribute: reports per month)
 *   MG8 — Review moderation      (entity: reviews, attribute: avg hours to approve)
 *
 * Chapter 4 Investigation Techniques supported:
 *   - Formal Experiment: logExperimentObservation(), computeExperimentResults()
 *   - Survey:            logSurveyResponse()
 *   - Case Study:        logCaseStudyEvent()
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once 'config.php';

class MetricsLogger {

    private PDO $db;

    public function __construct() {
        $database  = new Database();
        $this->db  = $database->getConnection();
    }

    // =========================================================================
    // CHAPTER 3: GQM LOGGING METHODS
    // Each method = one data element feeding into a GQ(I)M indicator
    // =========================================================================

    /**
     * MG1 — Log a tracker calculation event.
     * Indicator I1: Error rate chart (errors per 100 uses, last 30 days)
     * Data element: tracker_logs row
     * Scale: ratio | Range: 0–100 | Precision: 2 decimal places
     */
    public function logTrackerUse(
        ?int   $userId,
        string $lastPeriodDate,
        int    $week,
        string $dueDate,
        bool   $isError     = false,
        ?string $errorReason = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tracker_logs
                 (user_id, last_period_date, calculated_week, calculated_due_date, is_error, error_reason)
                 VALUES (:uid, :lpd, :week, :due, :err, :reason)"
            );
            $stmt->execute([
                ':uid'    => $userId,
                ':lpd'    => $lastPeriodDate,
                ':week'   => $week,
                ':due'    => $dueDate,
                ':err'    => $isError ? 1 : 0,
                ':reason' => $errorReason,
            ]);

            // Chapter 4: auto-record as case study event (Second Degree Contact)
            $this->logCaseStudyEvent(
                'feature_use',
                'Pregnancy Tracker Used',
                $isError ? "Error: $errorReason" : "Week $week calculated, due date $dueDate",
                $userId,
                $isError ? 'medium' : 'low',
                'M1.1'
            );

        } catch (\Exception $e) {
            $this->logSystemError('MetricsLogger/logTrackerUse', $e->getMessage(), $userId);
        }
    }

    /**
     * MG3 — Log a page view for the registration funnel.
     * Indicator I3: Registration funnel bar (signup visits vs actual registrations)
     * Data element: page_views row
     */
    public function logPageView(string $page, ?int $userId = null): void {
        try {
            $sessionId = session_id() ?: null;
            $stmt = $this->db->prepare(
                "INSERT INTO page_views (page, session_id, user_id) VALUES (:page, :sid, :uid)"
            );
            $stmt->execute([':page' => $page, ':sid' => $sessionId, ':uid' => $userId]);
        } catch (\Exception $e) {
            // Silent — page view logging must never interrupt user flow
        }
    }

    /**
     * MG6 — Log a system error.
     * Indicator I6: Error log table (errors per endpoint, last 30 days)
     * Data element: system_errors row
     * Severity scale: low / medium / high / critical
     */
    public function logSystemError(
        string  $endpoint,
        string  $errorMsg,
        ?int    $userId   = null,
        string  $severity = 'medium'
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO system_errors (endpoint, error_msg, severity, user_id)
                 VALUES (:ep, :msg, :sev, :uid)"
            );
            $stmt->execute([
                ':ep'  => $endpoint,
                ':msg' => substr($errorMsg, 0, 2000),
                ':sev' => $severity,
                ':uid' => $userId,
            ]);
        } catch (\Exception $e) {
            error_log("[MHU MetricsLogger] $endpoint: $errorMsg");
        }
    }

    /**
     * MG7 — Log a content feedback / inaccuracy report.
     * Indicator I8: Feedback count per month
     * Data element: content_feedback row
     */
    public function logContentFeedback(
        string $type,
        string $message,
        ?int   $weekNumber = null,
        ?int   $userId     = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO content_feedback (feedback_type, week_number, message, user_id)
                 VALUES (:type, :week, :msg, :uid)"
            );
            $stmt->execute([
                ':type' => $type,
                ':week' => $weekNumber,
                ':msg'  => $message,
                ':uid'  => $userId,
            ]);

            // Case study event: user feedback = high impact for content quality measurement
            $this->logCaseStudyEvent(
                'user_feedback',
                'Content Feedback Submitted',
                "Type: $type" . ($weekNumber ? ", Week: $weekNumber" : ''),
                $userId,
                'medium',
                'M7.2'
            );

        } catch (\Exception $e) {
            $this->logSystemError('MetricsLogger/logContentFeedback', $e->getMessage(), $userId);
        }
    }

    /**
     * MG5 — Log an authentication event (login/signup).
     * Indicator I5: Bcrypt compliance percentage
     * Data element: auth_audit_log row
     */
    public function logAuthEvent(string $eventType, ?int $userId = null): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $stmt = $this->db->prepare(
                "INSERT INTO auth_audit_log (user_id, event_type, ip_address) VALUES (:uid, :ev, :ip)"
            );
            $stmt->execute([':uid' => $userId, ':ev' => $eventType, ':ip' => $ip]);
        } catch (\Exception $e) {
            // Silent
        }
    }

    // =========================================================================
    // CHAPTER 4: EMPIRICAL INVESTIGATION METHODS
    // Implements all 3 investigation techniques from Chapter 4
    // =========================================================================

    /**
     * FORMAL EXPERIMENT — Record one observation (DC1: fully defined measure)
     * Ch4: Data Collection guideline DC1
     *   - entity + attribute + unit + counting rule fully specified
     * Ch4: Contact degrees: first (direct), second (monitoring), third (artifact)
     *
     * @param int    $experimentId  The experiment this observation belongs to
     * @param string $metricName    DC1: name of the measure
     * @param float  $metricValue   DC1: measured value
     * @param string $metricUnit    DC1: unit of measurement
     * @param string $groupName     D3: treatment group (e.g. 'control', 'treatment')
     * @param string $blockName     Principle 3: blocking group (e.g. 'university_x')
     * @param string $contactDegree first|second|third
     * @param int    $subjectId     D1: the participant
     * @param string $notes         DC3: dropout notes, context
     */
    public function logExperimentObservation(
        int    $experimentId,
        string $metricName,
        float  $metricValue,
        string $metricUnit,
        string $groupName     = 'default',
        string $blockName     = '',
        string $contactDegree = 'second',
        ?int   $subjectId     = null,
        string $notes         = ''
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO experiment_observations
                 (experiment_id, subject_id, metric_name, metric_value, metric_unit,
                  group_name, block_name, contact_degree, notes)
                 VALUES (:eid, :sid, :mn, :mv, :mu, :grp, :blk, :cd, :nt)"
            );
            $stmt->execute([
                ':eid' => $experimentId,
                ':sid' => $subjectId,
                ':mn'  => $metricName,
                ':mv'  => $metricValue,
                ':mu'  => $metricUnit,
                ':grp' => $groupName,
                ':blk' => $blockName ?: null,
                ':cd'  => $contactDegree,
                ':nt'  => $notes ?: null,
            ]);
        } catch (\Exception $e) {
            $this->logSystemError('MetricsLogger/logExperimentObservation', $e->getMessage());
        }
    }

    /**
     * FORMAL EXPERIMENT — Compute and store analysis results
     * Ch4: Analysis guidelines A1-A5 + Presentation P2, P4
     * Computes: mean, std dev, min, max, n per group
     * Admin fills in: p_value, effect_size, conclusion, limitations manually
     * via the dashboard or API
     */
    public function computeExperimentResults(int $experimentId, string $metricName): array {
        try {
            // Fetch all observations for this experiment + metric
            $stmt = $this->db->prepare(
                "SELECT group_name, metric_value
                 FROM experiment_observations
                 WHERE experiment_id = :eid AND metric_name = :mn
                 ORDER BY group_name"
            );
            $stmt->execute([':eid' => $experimentId, ':mn' => $metricName]);
            $rows = $stmt->fetchAll();

            if (empty($rows)) return [];

            // Group by treatment group
            $groups = [];
            foreach ($rows as $row) {
                $groups[$row['group_name']][] = (float) $row['metric_value'];
            }

            $results = [];
            foreach ($groups as $groupName => $values) {
                $n    = count($values);
                $mean = array_sum($values) / $n;
                $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / max($n - 1, 1);
                $std  = sqrt($variance);
                $min  = min($values);
                $max  = max($values);

                // Upsert result row
                $upsert = $this->db->prepare(
                    "INSERT INTO experiment_results
                     (experiment_id, metric_name, group_name, n_observations, mean_value,
                      std_deviation, min_value, max_value)
                     VALUES (:eid, :mn, :grp, :n, :mean, :std, :min, :max)
                     ON DUPLICATE KEY UPDATE
                       n_observations=VALUES(n_observations),
                       mean_value=VALUES(mean_value),
                       std_deviation=VALUES(std_deviation),
                       min_value=VALUES(min_value),
                       max_value=VALUES(max_value),
                       computed_at=NOW()"
                );
                $upsert->execute([
                    ':eid'  => $experimentId, ':mn'   => $metricName,
                    ':grp'  => $groupName,    ':n'    => $n,
                    ':mean' => round($mean,4), ':std'  => round($std,4),
                    ':min'  => $min,           ':max'  => $max,
                ]);

                $results[$groupName] = [
                    'n' => $n, 'mean' => round($mean,4), 'std' => round($std,4),
                    'min' => $min, 'max' => $max,
                ];
            }
            return $results;

        } catch (\Exception $e) {
            $this->logSystemError('MetricsLogger/computeExperimentResults', $e->getMessage());
            return [];
        }
    }

    /**
     * SURVEY — Record a user survey response
     * Ch4: Survey = retrospective study, "investigate in the large"
     * Used to measure: user satisfaction, usability perception, feature usefulness
     * Maps to GQM: QA4 (customer satisfaction), QB (ease of use), QC (defect perception)
     */
    public function logSurveyResponse(
        string $surveyName,
        array  $answers,
        ?int   $userId = null
    ): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO survey_responses
                 (survey_name, user_id, q_tracker_useful, q_tips_accurate,
                  q_site_easy_to_use, q_would_recommend, q_overall_satisfy, open_comment)
                 VALUES (:sn, :uid, :q1, :q2, :q3, :q4, :q5, :cmt)"
            );
            $stmt->execute([
                ':sn'  => $surveyName,
                ':uid' => $userId,
                ':q1'  => $answers['tracker_useful']   ?? null,
                ':q2'  => $answers['tips_accurate']    ?? null,
                ':q3'  => $answers['site_easy_to_use'] ?? null,
                ':q4'  => $answers['would_recommend']  ?? null,
                ':q5'  => $answers['overall_satisfy']  ?? null,
                ':cmt' => $answers['open_comment']     ?? null,
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logSystemError('MetricsLogger/logSurveyResponse', $e->getMessage(), $userId);
            return false;
        }
    }

    /**
     * CASE STUDY — Record a real-world usage event
     * Ch4: Case study = "research in the typical"
     * Documents actual events for post-hoc analysis
     * Contact degree: First (user-reported) or Second (system-detected)
     */
    public function logCaseStudyEvent(
        string  $category,
        string  $eventName,
        string  $description  = '',
        ?int    $userId       = null,
        string  $impactLevel  = 'low',
        ?string $relatedMetric = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO case_study_events
                 (event_category, event_name, description, user_id, impact_level, related_metric)
                 VALUES (:cat, :name, :desc, :uid, :imp, :met)"
            );
            $stmt->execute([
                ':cat'  => $category,
                ':name' => $eventName,
                ':desc' => $description,
                ':uid'  => $userId,
                ':imp'  => $impactLevel,
                ':met'  => $relatedMetric,
            ]);
        } catch (\Exception $e) {
            // Silent — case study logging must not break user flow
        }
    }

    // =========================================================================
    // DASHBOARD SUMMARY — fetches all 8 GQM indicators + Chapter 4 data
    // =========================================================================

    /**
     * Returns all data needed to render the full admin dashboard.
     * Covers GQM Indicators I1–I8 and Chapter 4 investigation summaries.
     */
    public function getDashboardSummary(): array {
        $summary = [];

        // ── GQM Indicators ────────────────────────────────────────────────────

        // I1: Tracker accuracy (MG1)
        $summary['tracker_accuracy'] = $this->fetchOne(
            "SELECT * FROM v_tracker_accuracy",
            ['total_uses' => 0, 'error_count' => 0, 'error_rate_pct' => 0]
        );

        // I2: Health tip coverage (MG2)
        $summary['tips_coverage'] = $this->fetchOne(
            "SELECT * FROM v_tips_coverage",
            ['total_tips' => 0, 'reviewed_tips' => 0, 'reviewed_pct' => 0]
        );

        // I3: Registration funnel (MG3)
        $summary['registration_funnel'] = $this->fetchOne(
            "SELECT * FROM v_registration_funnel",
            ['signup_page_visits' => 0, 'registered_users' => 0, 'completion_rate_pct' => 0]
        );

        // I4: Return visits (MG4)
        $summary['return_visits'] = $this->fetchOne(
            "SELECT * FROM v_return_visits",
            ['total_users' => 0, 'active_last_7_days' => 0, 'weekly_active_pct' => 0]
        );

        // I5: Password security (MG5)
        $summary['password_security'] = $this->fetchOne(
            "SELECT * FROM v_password_security",
            ['total_users' => 0, 'bcrypt_count' => 0, 'compliance_pct' => 100]
        );

        // I6: System error summary (MG6)
        try {
            $rows = $this->db->query(
                "SELECT COUNT(*) AS total_errors,
                        SUM(CASE WHEN severity='critical' OR severity='high' THEN 1 ELSE 0 END) AS critical_errors,
                        COUNT(DISTINCT endpoint) AS affected_endpoints
                 FROM system_errors
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )->fetch();
            $summary['system_errors'] = $rows ?: ['total_errors' => 0, 'critical_errors' => 0, 'affected_endpoints' => 0];
        } catch (\Exception $e) {
            $summary['system_errors'] = ['error' => $e->getMessage()];
        }

        // I7: Review moderation speed (MG8)
        $summary['review_moderation'] = $this->fetchOne(
            "SELECT * FROM v_review_moderation",
            ['total_reviews' => 0, 'approved_reviews' => 0, 'approval_rate_pct' => 0, 'avg_hours_to_approve' => null]
        );

        // I8: Content feedback (MG7)
        $summary['content_feedback'] = $this->fetchOne(
            "SELECT * FROM v_content_feedback",
            ['total_reports' => 0, 'open_reports' => 0, 'resolved_reports' => 0]
        );

        // ── Chapter 4: Empirical Investigation Summaries ─────────────────────

        // Active experiments (formal experiments + case studies + surveys)
        try {
            $summary['experiments'] = $this->db->query(
                "SELECT experiment_id, title, investigation_type, phase,
                        total_observations, total_subjects, started_at
                 FROM v_experiment_summary ORDER BY started_at DESC LIMIT 5"
            )->fetchAll();
        } catch (\Exception $e) {
            $summary['experiments'] = [];
        }

        // Survey baseline averages
        try {
            $summary['survey_results'] = $this->db->query(
                "SELECT * FROM v_survey_results ORDER BY total_responses DESC LIMIT 5"
            )->fetchAll();
        } catch (\Exception $e) {
            $summary['survey_results'] = [];
        }

        // Recent case study events (last 10)
        try {
            $summary['case_study_events'] = $this->db->query(
                "SELECT event_category, event_name, impact_level, related_metric, occurred_at
                 FROM case_study_events ORDER BY occurred_at DESC LIMIT 10"
            )->fetchAll();
        } catch (\Exception $e) {
            $summary['case_study_events'] = [];
        }

        return $summary;
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    private function fetchOne(string $sql, array $fallback = []): array {
        try {
            $row = $this->db->query($sql)->fetch();
            return $row ?: $fallback;
        } catch (\Exception $e) {
            return array_merge($fallback, ['error' => $e->getMessage()]);
        }
    }
}

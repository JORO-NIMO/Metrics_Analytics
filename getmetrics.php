<?php
/**
 * backend/getexperiments.php
 * Chapter 4: Returns experiment registry and analysis results
 * Admin-only endpoint
 */
session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit();
}

$database = new Database();
$db       = $database->getConnection();

try {
    // All experiments with observation counts
    $experiments = $db->query("SELECT * FROM v_experiment_summary ORDER BY started_at DESC")->fetchAll();

    // Results for each experiment
    $results = $db->query(
        "SELECT er.*, e.title AS experiment_title
         FROM experiment_results er
         JOIN experiments e ON er.experiment_id = e.experiment_id
         ORDER BY er.computed_at DESC"
    )->fetchAll();

    // Survey aggregates
    $surveys = $db->query("SELECT * FROM v_survey_results")->fetchAll();

    // Case study timeline
    $caseEvents = $db->query(
        "SELECT * FROM case_study_events ORDER BY occurred_at DESC LIMIT 50"
    )->fetchAll();

    echo json_encode([
        'success'     => true,
        'experiments' => $experiments,
        'results'     => $results,
        'surveys'     => $surveys,
        'case_events' => $caseEvents
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

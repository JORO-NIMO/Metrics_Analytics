<?php
/**
 * backend/computeresults.php
 * Chapter 4: Triggers statistical computation for a specific experiment
 * Computes: mean, std dev, min, max, n for each treatment group
 * Admin-only
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit();
}

$experimentId = intval($_POST['experiment_id'] ?? 0);
$metricName   = trim($_POST['metric_name'] ?? '');

if (!$experimentId || empty($metricName)) {
    echo json_encode(['success' => false, 'error' => 'experiment_id and metric_name required']);
    exit();
}

$logger  = new MetricsLogger();
$results = $logger->computeExperimentResults($experimentId, $metricName);

if (!empty($results)) {
    echo json_encode(['success' => true, 'results' => $results,
        'message' => 'Statistics computed. Check experiment_results table for full details.']);
} else {
    echo json_encode(['success' => false, 'error' => 'No observations found for this experiment and metric.']);
}
?>

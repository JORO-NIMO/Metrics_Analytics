<?php
/**
 * backend/getmetrics.php
 * Returns all GQM indicators (I1-I8) + Chapter 4 empirical investigation data
 * Admin-only endpoint used by the metrics dashboard
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

try {
    $logger = new MetricsLogger();
    $data   = $logger->getDashboardSummary();
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

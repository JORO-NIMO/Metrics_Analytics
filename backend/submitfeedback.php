<?php
// backend/submitfeedback.php
// Accepts content feedback / inaccuracy reports from the frontend
// Corresponds to GQM M7.2 — user-reported inaccuracies per month

session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$type    = $_POST['type']    ?? 'general';
$message = trim($_POST['message'] ?? '');
$week    = isset($_POST['week']) ? (int)$_POST['week'] : null;
$userId  = $_SESSION['user_id'] ?? null;

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Message is required']);
    exit();
}

if (!in_array($type, ['tip_error', 'general'])) {
    $type = 'general';
}

if ($week !== null && ($week < 1 || $week > 42)) {
    $week = null;
}

try {
    $logger = new MetricsLogger();
    $logger->logContentFeedback($type, $message, $week, $userId);
    echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Could not save feedback']);
}
?>

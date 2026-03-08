<?php
// backend/logpageview.php
// GQM M3.1, M3.2 — Log page views for the registration funnel
session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

$page   = trim($_POST['page'] ?? '');
$userId = $_SESSION['user_id'] ?? null;

$allowedPages = ['signup', 'login', 'index', 'tracker', 'review'];
if (!in_array($page, $allowedPages)) {
    echo json_encode(['success' => false, 'error' => 'Invalid page']);
    exit();
}

try {
    $logger = new MetricsLogger();
    $logger->logPageView($page, $userId);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
?>

<?php
// backend/savetracker.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$user_id     = $_SESSION['user_id'];
$last_period = $_POST['last_period'] ?? '';

if (empty($last_period)) {
    echo json_encode(['success' => false, 'error' => 'Last period date is required']);
    exit();
}

// Calculate due date (40 weeks = 280 days)
$last_period_date = new DateTime($last_period);
$due_date         = clone $last_period_date;
$due_date->modify('+280 days');

// Calculate current week
$today          = new DateTime();
$interval       = $today->diff($last_period_date);
$days_pregnant  = $interval->days;
$current_week   = (int) floor($days_pregnant / 7);

$database = new Database();
$db = $database->getConnection();

// Upsert tracking record
$check = $db->prepare("SELECT track_id FROM pregnancy_tracking WHERE user_id = :user_id");
$check->execute([':user_id' => $user_id]);

if ($check->rowCount() > 0) {
    $stmt = $db->prepare("UPDATE pregnancy_tracking SET last_period_date=:lp, due_date=:dd, current_week=:cw WHERE user_id=:uid");
} else {
    $stmt = $db->prepare("INSERT INTO pregnancy_tracking (user_id, last_period_date, due_date, current_week) VALUES (:uid,:lp,:dd,:cw)");
}

$stmt->execute([
    ':uid' => $user_id,
    ':lp'  => $last_period,
    ':dd'  => $due_date->format('Y-m-d'),
    ':cw'  => $current_week
]);

// Get health tip for this week
$tip_stmt = $db->prepare("SELECT title, content FROM health_tips WHERE week_number = :week");
$tip_stmt->execute([':week' => $current_week]);
$tip = $tip_stmt->fetch();

echo json_encode([
    'success'       => true,
    'due_date'      => $due_date->format('Y-m-d'),
    'current_week'  => $current_week,
    'days_pregnant' => $days_pregnant,
    'health_tip'    => $tip ?: ['title' => 'Keep Going!', 'content' => 'You are doing great. Stay healthy and keep attending your prenatal visits.']
]);
?>

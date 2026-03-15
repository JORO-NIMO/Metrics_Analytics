<?php
session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

$logger = new MetricsLogger();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$user_id     = $_SESSION['user_id'];
$last_period = trim($_POST['last_period'] ?? '');

if (empty($last_period)) {
    echo json_encode(['success' => false, 'error' => 'Please enter your last period date']);
    exit();
}

$isError = false;
$errorReason = null;

try {
    $last_period_date = new DateTime($last_period);
    $today            = new DateTime();
    $daysAgo          = (int) $today->diff($last_period_date)->days;

    if ($last_period_date > $today) {
        $isError = true;
        $errorReason = 'Last period date cannot be in the future';
    } elseif ($daysAgo > 294) {
        $isError = true;
        $errorReason = 'Date is more than 42 weeks ago';
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Invalid date format']);
    exit();
}

$due_date      = clone $last_period_date;
$due_date->modify('+280 days');
$days_pregnant = (int) (new DateTime())->diff($last_period_date)->days;
$current_week  = (int) floor($days_pregnant / 7);

$logger->logTrackerUse($user_id, $last_period, $current_week, $due_date->format('Y-m-d'), $isError, $errorReason);

if ($isError) {
    echo json_encode(['success' => false, 'error' => $errorReason]);
    exit();
}

$database = new Database();
$db       = $database->getConnection();

$check = $db->prepare("SELECT track_id FROM pregnancy_tracking WHERE user_id = :uid");
$check->execute([':uid' => $user_id]);

if ($check->rowCount() > 0) {
    $stmt = $db->prepare(
        "UPDATE pregnancy_tracking SET last_period_date=:lp, due_date=:dd, current_week=:cw WHERE user_id=:uid"
    );
} else {
    $stmt = $db->prepare(
        "INSERT INTO pregnancy_tracking (user_id, last_period_date, due_date, current_week) VALUES (:uid,:lp,:dd,:cw)"
    );
}
$stmt->execute([
    ':uid' => $user_id,
    ':lp'  => $last_period,
    ':dd'  => $due_date->format('Y-m-d'),
    ':cw'  => $current_week
]);

$tip_stmt = $db->prepare("SELECT title, content FROM health_tips WHERE week_number = :week LIMIT 1");
$tip_stmt->execute([':week' => $current_week]);
$tip = $tip_stmt->fetch();

echo json_encode([
    'success'       => true,
    'due_date'      => $due_date->format('Y-m-d'),
    'current_week'  => $current_week,
    'days_pregnant' => $days_pregnant,
    'health_tip'    => $tip ?: [
        'title'   => 'Keep Going!',
        'content' => 'You are doing amazing. Stay healthy and attend all your prenatal visits.'
    ]
]);
?>

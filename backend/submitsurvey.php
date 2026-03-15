<?php
/**
 * backend/submitsurvey.php
 * Chapter 4: Survey investigation technique endpoint
 * Survey = retrospective study / "investigate in the large"
 * Questions map directly to GQM measurement goals (MG1-MG8)
 */
session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$userId     = $_SESSION['user_id'] ?? null;
$surveyName = trim($_POST['survey_name'] ?? 'User Satisfaction Survey');
$answers    = [
    'tracker_useful'   => isset($_POST['q1']) ? intval($_POST['q1']) : null,
    'tips_accurate'    => isset($_POST['q2']) ? intval($_POST['q2']) : null,
    'site_easy_to_use' => isset($_POST['q3']) ? intval($_POST['q3']) : null,
    'would_recommend'  => isset($_POST['q4']) ? intval($_POST['q4']) : null,
    'overall_satisfy'  => isset($_POST['q5']) ? intval($_POST['q5']) : null,
    'open_comment'     => trim($_POST['comment'] ?? ''),
];

// Validate Likert scale answers (1-5)
foreach (['tracker_useful','tips_accurate','site_easy_to_use','would_recommend','overall_satisfy'] as $key) {
    if ($answers[$key] !== null && ($answers[$key] < 1 || $answers[$key] > 5)) {
        echo json_encode(['success' => false, 'error' => "Invalid value for $key"]);
        exit();
    }
}

$logger = new MetricsLogger();
$ok     = $logger->logSurveyResponse($surveyName, $answers, $userId);

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your feedback! Your response helps us improve.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Could not save survey response. Please try again.']);
}
?>

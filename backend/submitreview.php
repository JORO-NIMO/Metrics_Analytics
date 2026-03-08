<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please login to submit a review']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$user_id     = $_SESSION['user_id'];
$review_text = trim($_POST['review_text'] ?? '');
$rating      = intval($_POST['rating']    ?? 0);

if (empty($review_text)) {
    echo json_encode(['success' => false, 'error' => 'Review text is required']);
    exit();
}
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Rating must be between 1 and 5']);
    exit();
}

$database = new Database();
$db       = $database->getConnection();

$stmt = $db->prepare(
    "INSERT INTO reviews (user_id, review_text, rating, is_approved) VALUES (:uid, :text, :rating, 0)"
);
$stmt->execute([':uid' => $user_id, ':text' => $review_text, ':rating' => $rating]);

echo json_encode(['success' => true, 'message' => 'Review submitted! It will appear after approval.']);
?>

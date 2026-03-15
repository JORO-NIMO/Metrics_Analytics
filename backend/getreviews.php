<?php
// backend/getreviews.php
require_once 'config.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare(
    "SELECT r.review_text, r.rating, r.created_at, u.fullname
     FROM reviews r
     JOIN users u ON r.user_id = u.user_id
     WHERE r.is_approved = 1
     ORDER BY r.created_at DESC
     LIMIT 10"
);
$stmt->execute();
$reviews = $stmt->fetchAll();

echo json_encode($reviews);
?>

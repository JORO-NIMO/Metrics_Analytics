<?php
// submit_contact.php
// Handles contact form POST submissions.
//
// Measurement validation applied:
//   - Inputs are mapped to expected nominal/text attributes.
//   - Validation ensures the empirical relational system
//     is preserved: empty strings are not valid members of
//     the entity set for name, phone, or message.
//   - Conflicts (missing required fields) are detected and
//     returned as structured error responses — analogous to
//     measurement validation rejecting inconsistent data.

include "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

// --- Measurement validation: ensure required attributes have values ---
$name    = trim($_POST['name']    ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if (strlen($name) === 0)    $errors[] = "Name is required";
if (strlen($phone) === 0)   $errors[] = "Phone is required";
if (strlen($message) === 0) $errors[] = "Message is required";

// Phone validation: nominal attribute must match expected pattern
if ($phone !== '' && !preg_match('/^[+0-9\s\-]{7,20}$/', $phone)) {
    $errors[] = "Phone number format is invalid";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}

// --- Store in database ---
$stmt = $conn->prepare(
    "INSERT INTO contact_messages (name, phone, message) VALUES (?, ?, ?)"
);
$stmt->bind_param("sss", $name, $phone, $message);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to save message"]);
    exit;
}

// Direct measurement: increment form_submissions ratio-scale counter
$conn->query(
    "UPDATE platform_metrics SET metric_value = metric_value + 1,
     recorded_at = NOW() WHERE metric_name = 'total_form_submissions'"
);

echo json_encode(["status" => "success", "message" => "Message received"]);
?>
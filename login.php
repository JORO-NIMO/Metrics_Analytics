<?php
// backend/getuserdata.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id  = $_SESSION['user_id'];
$database = new Database();
$db       = $database->getConnection();

// User info
$u = $db->prepare("SELECT fullname, email, gender, created_at FROM users WHERE user_id = :id");
$u->execute([':id' => $user_id]);
$user = $u->fetch();

// Pregnancy tracking
$t = $db->prepare("SELECT * FROM pregnancy_tracking WHERE user_id = :id ORDER BY created_at DESC LIMIT 1");
$t->execute([':id' => $user_id]);
$tracking = $t->fetch();

// Appointments
$a = $db->prepare("SELECT * FROM appointments WHERE user_id = :id AND status = 'scheduled' ORDER BY appointment_date ASC LIMIT 5");
$a->execute([':id' => $user_id]);
$appointments = $a->fetchAll();

echo json_encode([
    'user'         => $user,
    'tracking'     => $tracking,
    'appointments' => $appointments
]);
?>

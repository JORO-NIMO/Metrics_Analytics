<?php
/**
 * backend/check_session.php
 * Validates user session and role for frontend access
 */
require_once 'session_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['valid' => false, 'error' => 'Not logged in']);
    exit();
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['valid' => false, 'error' => 'Admin access required']);
    exit();
}

// Session is valid - return user info
echo json_encode([
    'valid' => true,
    'user' => [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ]
]);
?>

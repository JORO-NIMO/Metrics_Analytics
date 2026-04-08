<?php
/**
 * backend/logout.php
 * Properly destroys session and logs out user
 */
require_once 'session_config.php';

// Log the logout event if user was logged in
if (isset($_SESSION['user_id'])) {
    require_once 'metrics_logger.php';
    $logger = new MetricsLogger();
    $logger->logAuthEvent('logout', $_SESSION['user_id']);
}

// Destroy all session data
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Return JSON response for AJAX calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} else {
    // Direct access - redirect to login
    header("Location: http://localhost:8080/MaternalHealthUganda/frontend/login.html");
}
exit();
?>

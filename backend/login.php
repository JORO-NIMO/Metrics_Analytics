<?php
require_once 'session_config.php';
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit();
}

$database = new Database();
$db       = $database->getConnection();
$logger   = new MetricsLogger();

$stmt = $db->prepare(
    "SELECT user_id, fullname, email, password, role
     FROM users WHERE email = :email AND is_active = 1"
);
$stmt->execute([':email' => $email]);

if ($stmt->rowCount() === 1) {
    $user = $stmt->fetch();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_email']= $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id")
           ->execute([':id' => $user['user_id']]);

        $logger->logAuthEvent('login_success', $user['user_id']);

        // Role-based redirect
        $redirectPage = ($user['role'] === 'admin') ? 'metrics_dashboard.html' : 'index.html';
        
        echo json_encode([
            'success'  => true,
            'redirect' => $redirectPage,
            'name'     => $user['fullname'],
            'role'     => $user['role']
        ]);
    } else {
        $logger->logAuthEvent('login_fail', $user['user_id']);
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    }
} else {
    $logger->logAuthEvent('login_fail', null);
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
}
?>

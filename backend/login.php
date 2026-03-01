<?php
// backend/login.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors   = [];

if (empty($email))    $errors[] = "Email is required";
if (empty($password)) $errors[] = "Password is required";

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT user_id, fullname, email, password, role FROM users WHERE email = :email AND is_active = 1");
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() === 1) {
    $user = $stmt->fetch();
    if (password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['user_name']  = $user['fullname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;

        // Update last login
        $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id")
           ->execute([':id' => $user['user_id']]);

        echo json_encode([
            'success'  => true,
            'redirect' => 'index.html',
            'name'     => $user['fullname'],
            'role'     => $user['role']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
}
?>

<?php
session_start();
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';
$gender   =      $_POST['gender']   ?? '';

$errors = [];
if (empty($fullname))                               $errors[] = 'Full name is required';
if (empty($email))                                  $errors[] = 'Email is required';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
if (empty($password))                               $errors[] = 'Password is required';
elseif (strlen($password) < 6)                      $errors[] = 'Password must be at least 6 characters';
if (empty($gender))                                 $errors[] = 'Gender is required';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit();
}

$database = new Database();
$db       = $database->getConnection();
$logger   = new MetricsLogger();

$check = $db->prepare("SELECT user_id FROM users WHERE email = :email");
$check->execute([':email' => $email]);
if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered. Please login.']);
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$insert = $db->prepare(
    "INSERT INTO users (fullname, email, password, gender) VALUES (:fullname, :email, :password, :gender)"
);
$insert->execute([
    ':fullname' => $fullname,
    ':email'    => $email,
    ':password' => $hashed,
    ':gender'   => $gender
]);

$newUserId = (int) $db->lastInsertId();
$logger->logAuthEvent('signup', $newUserId);

echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting to login...']);
?>

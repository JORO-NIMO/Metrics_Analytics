<?php
// backend/signup.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$fullname = trim($_POST['Fullname'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['Password']      ?? '';
$gender   = $_POST['gender']        ?? '';
$errors   = [];

if (empty($fullname))                              $errors[] = "Full name is required";
if (empty($email))                                 $errors[] = "Email is required";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
if (empty($password))                              $errors[] = "Password is required";
elseif (strlen($password) < 6)                     $errors[] = "Password must be at least 6 characters";
if (empty($gender))                                $errors[] = "Gender is required";

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Check duplicate email
$check = $db->prepare("SELECT user_id FROM users WHERE email = :email");
$check->execute([':email' => $email]);
if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered. Please login.']);
    exit();
}

// Insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$insert = $db->prepare("INSERT INTO users (fullname, email, password, gender) VALUES (:fullname, :email, :password, :gender)");
$insert->execute([
    ':fullname' => $fullname,
    ':email'    => $email,
    ':password' => $hashed,
    ':gender'   => $gender
]);

echo json_encode(['success' => true, 'message' => 'Registration successful! You can now login.']);
?>

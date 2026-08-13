<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Enter a valid email address.';
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

try {
    $statement = $pdo->prepare('SELECT id FROM user WHERE username = ? OR email = ?');
    $statement->execute([$username, $email]);

    if ($statement->fetch()) {
        $_SESSION['error'] = 'Username or email already exists.';
        header('Location: ../../frontend/pages/register_view.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $statement = $pdo->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'User')");
    $statement->execute([$username, $email, $hashedPassword]);

    $_SESSION['success'] = 'Registration successful. Please log in.';
    header('Location: ../../frontend/pages/login_view.php');
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: ../../frontend/pages/register_view.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Username or email already exists.';
            header('Location: ../../frontend/pages/register_view.php');
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'User')");
        $stmt->execute([$username, $email, $hashed_password]);

        $_SESSION['success'] = 'Registration successful. Please log in.';
        header('Location: ../../frontend/pages/login_view.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = 'An error occurred during registration.';
        header('Location: ../../frontend/pages/register_view.php');
        exit;
    }
} else {
    header('Location: ../../frontend/pages/register_view.php');
    exit;
}

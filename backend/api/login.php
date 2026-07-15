<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required.';
        header('Location: ../../frontend/pages/login_view.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ../../frontend/pages/home.php');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid username or password.';
            header('Location: ../../frontend/pages/login_view.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'An error occurred during login.';
        header('Location: ../../frontend/pages/login_view.php');
        exit;
    }
} else {
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

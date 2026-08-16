<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Username and password are required.';
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

if (mb_strlen($username) > 50) {
    $_SESSION['error'] = 'Username must be 50 characters or fewer.';
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

try {
    $statement = $pdo->prepare('SELECT id, username, password FROM user WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ../../frontend/pages/login_view.php');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    header('Location: ../../frontend/pages/home.php');
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Login failed. Please try again.';
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

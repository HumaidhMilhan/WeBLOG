<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/home.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['error'] = 'Blog not found.';
    header('Location: ../../frontend/pages/home.php');
    exit;
}

try {
    $statement = $pdo->prepare('DELETE FROM blogPost WHERE id = ? AND user_id = ?');
    $statement->execute([$id, $_SESSION['user_id']]);

    if ($statement->rowCount() === 0) {
        $_SESSION['error'] = 'You cannot delete this blog.';
        header('Location: ../../frontend/pages/home.php');
        exit;
    }

    $_SESSION['success'] = 'Blog deleted successfully.';
    header('Location: ../../frontend/pages/home.php');
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Blog could not be deleted.';
    header('Location: ../../frontend/pages/home.php');
    exit;
}

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

$blogId = filter_var($_POST['blog_id'] ?? '', FILTER_VALIDATE_INT);
$content = trim($_POST['content'] ?? '');

if (!$blogId) {
    $_SESSION['error'] = 'Blog not found.';
    header('Location: ../../frontend/pages/home.php');
    exit;
}

if ($content === '') {
    $_SESSION['error'] = 'Comment is required.';
    header('Location: ../../frontend/pages/blog_view.php?id=' . $blogId);
    exit;
}

if (mb_strlen($content) > 1000) {
    $_SESSION['error'] = 'Comment must be 1000 characters or fewer.';
    header('Location: ../../frontend/pages/blog_view.php?id=' . $blogId);
    exit;
}

try {
    $statement = $pdo->prepare('SELECT id FROM blogPost WHERE id = ?');
    $statement->execute([$blogId]);

    if (!$statement->fetch()) {
        $_SESSION['error'] = 'Blog not found.';
        header('Location: ../../frontend/pages/home.php');
        exit;
    }

    $statement = $pdo->prepare('INSERT INTO `comment` (blog_id, user_id, content) VALUES (?, ?, ?)');
    $statement->execute([$blogId, $_SESSION['user_id'], $content]);
    $_SESSION['success'] = 'Comment added.';
} catch (PDOException $error) {
    $_SESSION['error'] = 'Comment could not be added.';
}

header('Location: ../../frontend/pages/blog_view.php?id=' . $blogId);
exit;

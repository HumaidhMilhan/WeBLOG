<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/pages/login_view.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/blog_editor.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if (empty($title) || empty($content)) {
    $_SESSION['error'] = 'Title and content are required.';
    header('Location: ../../frontend/pages/blog_editor.php');
    exit;
}

if (strlen($title) > 255) {
    $_SESSION['error'] = 'Title must be 255 characters or fewer.';
    header('Location: ../../frontend/pages/blog_editor.php');
    exit;
}

if (mb_strlen($content) > 3000) {
    $_SESSION['error'] = 'Content must be 3000 characters or fewer.';
    header('Location: ../../frontend/pages/blog_editor.php');
    exit;
}

try {
    $statement = $pdo->prepare('INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)');
    $statement->execute([$_SESSION['user_id'], $title, $content]);
    $id = $pdo->lastInsertId();

    $_SESSION['success'] = 'Blog created successfully.';
    header('Location: ../../frontend/pages/blog_view.php?id=' . $id);
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Blog could not be created.';
    header('Location: ../../frontend/pages/blog_editor.php');
    exit;
}

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
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if (!$id || empty($title) || empty($content)) {
    $_SESSION['error'] = 'Title and content are required.';
    $location = $id ? '../../frontend/pages/blog_editor.php?id=' . $id : '../../frontend/pages/home.php';
    header('Location: ' . $location);
    exit;
}

if (strlen($title) > 255) {
    $_SESSION['error'] = 'Title must be 255 characters or fewer.';
    header('Location: ../../frontend/pages/blog_editor.php?id=' . $id);
    exit;
}

if (mb_strlen($content) > 3000) {
    $_SESSION['error'] = 'Content must be 3000 characters or fewer.';
    header('Location: ../../frontend/pages/blog_editor.php?id=' . $id);
    exit;
}

try {
    $statement = $pdo->prepare('SELECT id FROM blogPost WHERE id = ? AND user_id = ?');
    $statement->execute([$id, $_SESSION['user_id']]);

    if (!$statement->fetch()) {
        $_SESSION['error'] = 'You cannot edit this blog.';
        header('Location: ../../frontend/pages/home.php');
        exit;
    }

    $statement = $pdo->prepare('UPDATE blogPost SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $statement->execute([$title, $content, $id, $_SESSION['user_id']]);

    $_SESSION['success'] = 'Blog updated successfully.';
    header('Location: ../../frontend/pages/blog_view.php?id=' . $id);
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Blog could not be updated.';
    header('Location: ../../frontend/pages/blog_editor.php?id=' . $id);
    exit;
}

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

$commentId = filter_var($_POST['comment_id'] ?? '', FILTER_VALIDATE_INT);

if (!$commentId) {
    $_SESSION['error'] = 'Comment not found.';
    header('Location: ../../frontend/pages/home.php');
    exit;
}

try {
    $statement = $pdo->prepare('SELECT `comment`.id, `comment`.blog_id, `comment`.user_id AS comment_user_id, blogPost.user_id AS blog_user_id FROM `comment` INNER JOIN blogPost ON `comment`.blog_id = blogPost.id WHERE `comment`.id = ?');
    $statement->execute([$commentId]);
    $comment = $statement->fetch();

    if (!$comment) {
        $_SESSION['error'] = 'Comment not found.';
        header('Location: ../../frontend/pages/home.php');
        exit;
    }

    if ($_SESSION['user_id'] != $comment['comment_user_id'] && $_SESSION['user_id'] != $comment['blog_user_id']) {
        $_SESSION['error'] = 'You cannot delete this comment.';
        header('Location: ../../frontend/pages/blog_view.php?id=' . $comment['blog_id']);
        exit;
    }

    $statement = $pdo->prepare('DELETE FROM `comment` WHERE id = ?');
    $statement->execute([$commentId]);
    $_SESSION['success'] = 'Comment deleted.';
    header('Location: ../../frontend/pages/blog_view.php?id=' . $comment['blog_id']);
    exit;
} catch (PDOException $error) {
    $_SESSION['error'] = 'Comment could not be deleted.';
    header('Location: ../../frontend/pages/home.php');
    exit;
}

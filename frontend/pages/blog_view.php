<?php
session_start();
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/includes/markdown.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$blog = false;
$comments = [];

if ($id) {
    $statement = $pdo->prepare('SELECT blogPost.id, blogPost.user_id, blogPost.title, blogPost.content, blogPost.created_at, blogPost.updated_at, user.username FROM blogPost INNER JOIN user ON blogPost.user_id = user.id WHERE blogPost.id = ?');
    $statement->execute([$id]);
    $blog = $statement->fetch();

    if ($blog) {
        $statement = $pdo->prepare('SELECT `comment`.id, `comment`.user_id, `comment`.content, `comment`.created_at, user.username FROM `comment` INNER JOIN user ON `comment`.user_id = user.id WHERE `comment`.blog_id = ? ORDER BY `comment`.created_at ASC, `comment`.id ASC');
        $statement->execute([$id]);
        $comments = $statement->fetchAll();
    }
}

if (!$blog) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $blog ? htmlspecialchars($blog['title']) : 'Blog not found'; ?> - WeBLOG</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="site-title" href="home.php"><span>We</span>BLOG</a>
            <div class="nav-links">
                <a class="active-link" href="home.php">Home</a>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="blog_editor.php">Write a blog</a>
                    <a href="../../backend/api/logout.php">Logout</a>
                <?php } else { ?>
                    <a href="login_view.php">Login</a>
                    <a class="nav-button" href="register_view.php">Join WeBLOG</a>
                <?php } ?>
            </div>
        </nav>
    </header>

    <main class="page-container article-container">
        <?php if (!$blog) { ?>
            <div class="empty-message">
                <h1>Blog not found</h1>
                <a class="read-link" href="home.php">Return to home</a>
            </div>
        <?php } else { ?>
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="alert alert-error">
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success">
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php } ?>

            <article class="single-blog">
                <header class="article-header">
                    <a class="back-link" href="home.php">← All stories</a>
                    <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
                    <p class="blog-meta">
                        Written by <strong><?php echo htmlspecialchars($blog['username']); ?></strong>
                        <span>•</span>
                        <?php echo date('F j, Y', strtotime($blog['created_at'])); ?>
                    </p>
                </header>

                <div class="blog-content">
                    <?php echo render_markdown($blog['content']); ?>
                </div>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['user_id']) { ?>
                    <div class="blog-actions">
                        <a class="button-link secondary-button" href="blog_editor.php?id=<?php echo $blog['id']; ?>">Edit blog</a>
                        <form class="delete-form" action="../../backend/api/delete_blog.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                            <button class="danger-button" type="submit">Delete blog</button>
                        </form>
                    </div>
                <?php } ?>
            </article>

            <section class="comments-section">
                <div class="comments-heading">
                    <div>
                        <span class="eyebrow">Join the conversation</span>
                        <h2><?php echo count($comments); ?> <?php echo count($comments) === 1 ? 'Comment' : 'Comments'; ?></h2>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])) { ?>
                    <form class="comment-form" action="../../backend/api/add_comment.php" method="POST" novalidate>
                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                        <label for="comment-content">Add a comment</label>
                        <textarea id="comment-content" name="content" rows="4" maxlength="1000" placeholder="Share your thoughts" required></textarea>
                        <div class="comment-form-footer">
                            <span><span id="comment-count">0</span> / 1000</span>
                            <button class="btn comment-button" type="submit">Post comment</button>
                        </div>
                        <p class="form-error" id="comment-error"></p>
                    </form>
                <?php } else { ?>
                    <div class="login-prompt">
                        <p>Have something to add?</p>
                        <a class="read-link" href="login_view.php">Log in to comment</a>
                    </div>
                <?php } ?>

                <div class="comments-list">
                    <?php if (empty($comments)) { ?>
                        <div class="empty-comment">No comments yet. Start the conversation.</div>
                    <?php } else { ?>
                        <?php foreach ($comments as $comment) { ?>
                            <article class="comment-card">
                                <div class="comment-avatar"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($comment['username'], 0, 1))); ?></div>
                                <div class="comment-body">
                                    <div class="comment-meta">
                                        <div>
                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                            <span><?php echo date('M j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['user_id'] == $blog['user_id'])) { ?>
                                            <form class="comment-delete-form" action="../../backend/api/delete_comment.php" method="POST">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit">Delete</button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                </div>
                            </article>
                        <?php } ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    </main>
</body>
</html>

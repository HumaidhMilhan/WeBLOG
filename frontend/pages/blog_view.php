<?php
session_start();
require_once __DIR__ . '/../../backend/config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$blog = false;

if ($id) {
    $statement = $pdo->prepare('SELECT blogPost.id, blogPost.user_id, blogPost.title, blogPost.content, blogPost.created_at, blogPost.updated_at, user.username FROM blogPost INNER JOIN user ON blogPost.user_id = user.id WHERE blogPost.id = ?');
    $statement->execute([$id]);
    $blog = $statement->fetch();
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
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="site-title" href="home.php">WeBLOG</a>
            <div class="nav-links">
                <a href="home.php">Home</a>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="blog_editor.php">Create Blog</a>
                    <a href="../../backend/api/logout.php">Logout</a>
                <?php } else { ?>
                    <a href="login_view.php">Login</a>
                    <a href="register_view.php">Register</a>
                <?php } ?>
            </div>
        </nav>
    </header>

    <main class="page-container">
        <?php if (!$blog) { ?>
            <div class="empty-message">
                <h1>Blog not found</h1>
                <a class="read-link" href="home.php">Return to home</a>
            </div>
        <?php } else { ?>
            <?php if (isset($_SESSION['success'])) { ?>
                <div class="alert alert-success">
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php } ?>

            <article class="single-blog">
                <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
                <p class="blog-meta">
                    By <?php echo htmlspecialchars($blog['username']); ?> on
                    <?php echo date('F j, Y', strtotime($blog['created_at'])); ?>
                </p>
                <div class="blog-content">
                    <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                </div>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['user_id']) { ?>
                    <div class="blog-actions">
                        <a class="button-link" href="blog_editor.php?id=<?php echo $blog['id']; ?>">Edit Blog</a>
                        <form action="../../backend/api/delete_blog.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                            <button class="danger-button" type="submit">Delete Blog</button>
                        </form>
                    </div>
                <?php } ?>
            </article>
        <?php } ?>
    </main>
</body>
</html>

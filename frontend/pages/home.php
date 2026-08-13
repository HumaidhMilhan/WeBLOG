<?php
session_start();
require_once __DIR__ . '/../../backend/config/db.php';

$statement = $pdo->query('SELECT blogPost.id, blogPost.title, blogPost.created_at, user.username FROM blogPost INNER JOIN user ON blogPost.user_id = user.id ORDER BY blogPost.created_at DESC');
$blogs = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - WeBLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="site-title" href="home.php">WeBLOG</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
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

        <div class="page-heading">
            <div>
                <h1>Latest Blogs</h1>
                <p>Read posts shared by WeBLOG users.</p>
            </div>
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a class="button-link" href="blog_editor.php">Create Blog</a>
            <?php } ?>
        </div>

        <?php if (empty($blogs)) { ?>
            <div class="empty-message">No blogs found.</div>
        <?php } else { ?>
            <div class="blog-list">
                <?php foreach ($blogs as $blog) { ?>
                    <article class="blog-card">
                        <h2>
                            <a href="blog_view.php?id=<?php echo $blog['id']; ?>">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>
                        </h2>
                        <p class="blog-meta">
                            By <?php echo htmlspecialchars($blog['username']); ?> on
                            <?php echo date('F j, Y', strtotime($blog['created_at'])); ?>
                        </p>
                        <a class="read-link" href="blog_view.php?id=<?php echo $blog['id']; ?>">Read blog</a>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </main>
</body>
</html>

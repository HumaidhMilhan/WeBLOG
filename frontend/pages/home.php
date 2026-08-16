<?php
session_start();
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/includes/markdown.php';

$statement = $pdo->query('SELECT blogPost.id, blogPost.title, blogPost.content, blogPost.created_at, user.username, COUNT(`comment`.id) AS comment_count FROM blogPost INNER JOIN user ON blogPost.user_id = user.id LEFT JOIN `comment` ON blogPost.id = `comment`.blog_id GROUP BY blogPost.id, blogPost.title, blogPost.content, blogPost.created_at, user.username ORDER BY blogPost.created_at DESC');
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
            <a class="site-title" href="home.php">We<span>BLOG</span></a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <span class="welcome-text">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="blog_editor.php">Write a blog</a>
                    <a href="../../backend/api/logout.php">Logout</a>
                <?php } else { ?>
                    <a href="login_view.php">Login</a>
                    <a class="nav-button" href="register_view.php">Join WeBLOG</a>
                <?php } ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <span class="eyebrow">Stories worth sharing</span>
                <h1>Ideas, experiences and perspectives from our community.</h1>
                <p>Discover the latest writing from WeBLOG members or share a story of your own.</p>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a class="button-link" href="blog_editor.php">Start writing</a>
                <?php } else { ?>
                    <a class="button-link" href="register_view.php">Create an account</a>
                <?php } ?>
            </div>
            <div class="hero-detail">
                <span><?php echo count($blogs); ?></span>
                <p><?php echo count($blogs) === 1 ? 'story published' : 'stories published'; ?></p>
            </div>
        </section>

        <div class="page-container home-container">
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
                    <span class="eyebrow">Fresh from the community</span>
                    <h2>Latest blogs</h2>
                </div>
            </div>

            <?php if (empty($blogs)) { ?>
                <div class="empty-message">
                    <h2>No blogs yet</h2>
                    <p>Be the first person to share a story.</p>
                </div>
            <?php } else { ?>
                <div class="blog-list">
                    <?php foreach ($blogs as $blog) { ?>
                        <article class="blog-card">
                            <div class="card-topline">
                                <span><?php echo htmlspecialchars($blog['username']); ?></span>
                                <span><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></span>
                            </div>
                            <h3>
                                <a href="blog_view.php?id=<?php echo $blog['id']; ?>">
                                    <?php echo htmlspecialchars($blog['title']); ?>
                                </a>
                            </h3>
                            <p class="blog-excerpt"><?php echo htmlspecialchars(markdown_excerpt($blog['content'])); ?></p>
                            <div class="card-footer">
                                <a class="read-link" href="blog_view.php?id=<?php echo $blog['id']; ?>">Read story</a>
                                <span><?php echo $blog['comment_count']; ?> <?php echo $blog['comment_count'] == 1 ? 'comment' : 'comments'; ?></span>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>

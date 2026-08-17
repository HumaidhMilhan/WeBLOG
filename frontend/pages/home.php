<?php
session_start();
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/includes/markdown.php';

$statement = $pdo->query('SELECT blogPost.id, blogPost.title, blogPost.content, blogPost.created_at, user.username, COUNT(`comment`.id) AS comment_count FROM blogPost INNER JOIN user ON blogPost.user_id = user.id LEFT JOIN `comment` ON blogPost.id = `comment`.blog_id GROUP BY blogPost.id, blogPost.title, blogPost.content, blogPost.created_at, user.username ORDER BY blogPost.created_at DESC, blogPost.id DESC');
$blogs = $statement->fetchAll();
$featuredBlog = $blogs[0] ?? false;
$latestBlogs = $featuredBlog ? array_slice($blogs, 1) : [];
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
            <a class="site-title" href="home.php"><span>We</span>BLOG</a>
            <div class="nav-links">
                <a class="active-link" href="home.php">Home</a>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="blog_editor.php">Write</a>
                    <span class="welcome-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a class="nav-button" href="../../backend/api/logout.php">Logout</a>
                <?php } else { ?>
                    <a href="register_view.php">Register</a>
                    <a class="nav-button" href="login_view.php">Login</a>
                <?php } ?>
            </div>
        </nav>
    </header>

    <main>
        <div class="site-messages">
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
        </div>

        <section class="home-hero">
            <div class="hero-dots" aria-hidden="true"></div>
            <div class="hero-copy">
                <span class="eyebrow">Independent ideas and stories</span>
                <h1>Thoughts worth sharing.</h1>
                <p>Discover clear ideas and honest stories from our community.</p>
                <?php if ($featuredBlog) { ?>
                    <a class="button-link" href="blog_view.php?id=<?php echo $featuredBlog['id']; ?>">Read featured story</a>
                <?php } elseif (isset($_SESSION['user_id'])) { ?>
                    <a class="button-link" href="blog_editor.php">Write the first story</a>
                <?php } else { ?>
                    <a class="button-link" href="register_view.php">Join WeBLOG</a>
                <?php } ?>
            </div>
            <div class="hero-rings" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </section>

        <section class="stories-section" id="stories">
            <?php if (!$featuredBlog) { ?>
                <div class="empty-state">
                    <span class="eyebrow">Start the collection</span>
                    <h2>No stories have been published yet.</h2>
                    <p>Share the first idea with the WeBLOG community.</p>
                </div>
            <?php } else { ?>
                <div class="section-heading">
                    <span class="eyebrow">Selected from the community</span>
                    <h2>Featured story</h2>
                </div>

                <article class="featured-story">
                    <div class="featured-accent"></div>
                    <div class="featured-copy">
                        <h3>
                            <a href="blog_view.php?id=<?php echo $featuredBlog['id']; ?>">
                                <?php echo htmlspecialchars($featuredBlog['title']); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars(markdown_excerpt($featuredBlog['content'], 220)); ?></p>
                    </div>
                    <div class="featured-meta">
                        <span>By <?php echo htmlspecialchars($featuredBlog['username']); ?></span>
                        <span><?php echo date('F j, Y', strtotime($featuredBlog['created_at'])); ?></span>
                        <span><?php echo $featuredBlog['comment_count']; ?> <?php echo $featuredBlog['comment_count'] == 1 ? 'comment' : 'comments'; ?></span>
                    </div>
                    <a class="read-link featured-link" href="blog_view.php?id=<?php echo $featuredBlog['id']; ?>">Read story <span>→</span></a>
                </article>

                <?php if (!empty($latestBlogs)) { ?>
                    <div class="section-heading latest-heading">
                        <span class="eyebrow">More to explore</span>
                        <h2>Latest stories</h2>
                    </div>

                    <div class="stories-grid">
                        <?php foreach ($latestBlogs as $blog) { ?>
                            <article class="story-card">
                                <div class="story-card-meta">
                                    <span><?php echo date('M j, Y', strtotime($blog['created_at'])); ?></span>
                                    <span><?php echo $blog['comment_count']; ?> <?php echo $blog['comment_count'] == 1 ? 'comment' : 'comments'; ?></span>
                                </div>
                                <h3>
                                    <a href="blog_view.php?id=<?php echo $blog['id']; ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <p><?php echo htmlspecialchars(markdown_excerpt($blog['content'], 145)); ?></p>
                                <div class="story-card-footer">
                                    <span>By <?php echo htmlspecialchars($blog['username']); ?></span>
                                    <a class="read-link" href="blog_view.php?id=<?php echo $blog['id']; ?>">Read story <span>→</span></a>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </section>
    </main>
</body>
</html>

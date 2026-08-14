<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_view.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/db.php';

$blog = false;
$pageTitle = 'Create Blog';
$formAction = '../../backend/api/create_blog.php';

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        $_SESSION['error'] = 'Blog not found.';
        header('Location: home.php');
        exit;
    }

    $statement = $pdo->prepare('SELECT id, title, content FROM blogPost WHERE id = ? AND user_id = ?');
    $statement->execute([$id, $_SESSION['user_id']]);
    $blog = $statement->fetch();

    if (!$blog) {
        $_SESSION['error'] = 'You cannot edit this blog.';
        header('Location: home.php');
        exit;
    }

    $pageTitle = 'Edit Blog';
    $formAction = '../../backend/api/update_blog.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - WeBLOG</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="site-title" href="home.php">WeBLOG</a>
            <div class="nav-links">
                <a href="home.php">Home</a>
                <a href="../../backend/api/logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main class="editor-container">
        <h1><?php echo $pageTitle; ?></h1>

        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-error">
                <?php
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <form class="blog-form" action="<?php echo $formAction; ?>" method="POST">
            <?php if ($blog) { ?>
                <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
            <?php } ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" maxlength="255" value="<?php echo $blog ? htmlspecialchars($blog['title']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="14" required><?php echo $blog ? htmlspecialchars($blog['content']) : ''; ?></textarea>
            </div>

            <p class="form-error" id="form-error"></p>
            <button class="btn" type="submit"><?php echo $blog ? 'Update Blog' : 'Publish Blog'; ?></button>
        </form>
    </main>
</body>
</html>

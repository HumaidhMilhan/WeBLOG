<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WeBLOG</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <a class="auth-brand site-title" href="home.php"><span>We</span>BLOG</a>
        <span class="eyebrow">Welcome back</span>
        <h1>Login to your account</h1>
        <p class="auth-intro">Continue reading, writing and joining conversations.</p>
        
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

        <form class="login-form" action="../../backend/api/login.php" method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <p class="form-error" id="login-error"></p>
            <button type="submit" class="btn">Login</button>
        </form>
        <p class="auth-link">Don't have an account? <a href="register_view.php">Create one</a></p>
    </div>
</body>
</html>

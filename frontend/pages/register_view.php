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
    <title>Register - WeBLOG</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <a class="auth-brand site-title" href="home.php">We<span>BLOG</span></a>
        <span class="eyebrow">Join the community</span>
        <h1>Create your account</h1>
        <p class="auth-intro">Start publishing your ideas and connecting with other writers.</p>
        
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-error">
                <?php
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <form action="../../backend/api/register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="50" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p class="auth-link">Already have an account? <a href="login_view.php">Login here</a></p>
    </div>
</body>
</html>

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_view.php');
    exit;
}
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
    <main class="auth-container home-container">
        <h2>WeBLOG</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
        <p>You are logged in.</p>
        <a class="btn logout-link" href="../../backend/api/logout.php">Logout</a>
    </main>
</body>
</html>

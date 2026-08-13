<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: frontend/pages/home.php');
    exit;
}

header('Location: frontend/pages/login_view.php');
exit;

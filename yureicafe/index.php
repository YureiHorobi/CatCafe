<?php
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

redirect('login.php');
?>

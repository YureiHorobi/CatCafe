<?php
require_once __DIR__ . '/config/database.php';
log_activity($conn, 'Logout', 'User keluar dari sistem');
session_destroy();
redirect('login.php');
?>

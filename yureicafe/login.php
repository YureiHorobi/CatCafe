<?php
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, username, password, fullname, role FROM users WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && valid_login_password($password, $user['password'], $user['username'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            log_activity($conn, 'Login', 'User masuk ke sistem');
            redirect('dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cat Cafe</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-hero">
            <img class="login-logo" src="<?= url('assets/img/logo-cat-cafe.png') ?>" alt="Cat Cafe Logo">
            <h1>Cat Cafe</h1>
            <p>Sistem kasir Cat Cafe sederhana untuk mengelola menu, order, meja, dan laporan penjualan.</p>
            <div class="login-tips">
                <span>Dashboard</span>
                <span>Kasir</span>
                <span>Laporan</span>
            </div>
        </div>

        <div class="login-card">
            <h2>Masuk Sistem</h2>
            <p class="muted">Gunakan akun admin atau kasir yang sudah ada di database.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="contoh: admin" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="contoh: admin123" required>

                <button class="btn btn-primary btn-full" type="submit">Login</button>
            </form>

            <div class="default-account">
                <strong>Akun contoh:</strong><br>
                Admin: admin / admin123<br>
                Kasir: kasir / kasir123
            </div>
        </div>
    </div>
</body>
</html>

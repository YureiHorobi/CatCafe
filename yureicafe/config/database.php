<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi database XAMPP
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'pos_cafe';

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

// Base URL otomatis mengikuti nama folder project di htdocs
if (!defined('BASE_URL')) {
    $projectFolder = basename(dirname(__DIR__));
    define('BASE_URL', '/' . $projectFolder . '/');
}

function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($number)
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

function image_url($path)
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (preg_match('/^(https?:)?\/\//', $path) || substr($path, 0, 1) === '/' || substr($path, 0, 5) === 'data:') {
        return $path;
    }
    return url($path);
}

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function current_user_name()
{
    return $_SESSION['fullname'] ?? 'User';
}

function current_user_role()
{
    return $_SESSION['role'] ?? 'kasir';
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function flash_message()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
    }
}

function nav_active($keyword)
{
    $path = $_SERVER['SCRIPT_NAME'] ?? '';
    return strpos($path, '/' . $keyword . '/') !== false || strpos($path, $keyword . '.php') !== false ? 'active' : '';
}

function status_badge($status)
{
    $label = str_replace('_', ' ', (string) $status);
    $class = 'badge badge-secondary';

    if (in_array($status, ['available', 'completed'], true)) {
        $class = 'badge badge-success';
    } elseif (in_array($status, ['out_of_stock', 'occupied', 'processing', 'pending'], true)) {
        $class = 'badge badge-warning';
    } elseif (in_array($status, ['discontinued', 'cancelled'], true)) {
        $class = 'badge badge-danger';
    } elseif ($status === 'reserved') {
        $class = 'badge badge-info';
    }

    return '<span class="' . $class . '">' . h(ucwords($label)) . '</span>';
}

function log_activity($conn, $action, $description = '')
{
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
    $stmt = mysqli_prepare($conn, 'INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'isss', $userId, $action, $description, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Catatan: SQL bawaan menyebut admin123/kasir123, tetapi hash default sering sama dengan "password".
// Supaya mudah untuk tugas lokal, login menerima password hash valid atau password default tersebut.
function valid_login_password($input, $hash, $username)
{
    if (password_verify($input, $hash)) {
        return true;
    }

    $fallback = [
        'admin' => 'admin123',
        'kasir' => 'kasir123'
    ];

    return isset($fallback[$username]) && hash_equals($fallback[$username], $input);
}
?>

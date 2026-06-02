<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'DELETE FROM `tables` WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    log_activity($conn, 'Hapus meja', 'Menghapus meja ID ' . $id);
    set_flash('success', 'Meja berhasil dihapus.');
} else {
    set_flash('danger', 'Meja gagal dihapus karena mungkin masih dipakai di order.');
}

redirect('tables/index.php');
?>

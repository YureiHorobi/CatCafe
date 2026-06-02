<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'DELETE FROM categories WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    log_activity($conn, 'Hapus kategori', 'Menghapus kategori ID ' . $id);
    set_flash('success', 'Kategori berhasil dihapus.');
} else {
    set_flash('danger', 'Kategori gagal dihapus.');
}

redirect('categories/index.php');
?>

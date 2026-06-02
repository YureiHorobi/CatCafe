<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Edit Kategori';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'SELECT * FROM categories WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$category) {
    set_flash('danger', 'Kategori tidak ditemukan.');
    redirect('categories/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        set_flash('danger', 'Nama kategori wajib diisi.');
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE categories SET name = ?, description = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $description, $id);
        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'Edit kategori', 'Mengedit kategori ' . $name);
            set_flash('success', 'Kategori berhasil diperbarui.');
            redirect('categories/index.php');
        }
        set_flash('danger', 'Gagal memperbarui kategori.');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h3>Form Edit Kategori</h3>
        <a class="btn btn-light" href="<?= url('categories/index.php') ?>">Kembali</a>
    </div>

    <form method="POST" class="form-grid single">
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="name" required value="<?= h($category['name']) ?>">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" rows="4"><?= h($category['description']) ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Update</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

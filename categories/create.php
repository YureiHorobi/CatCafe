<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Tambah Kategori';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        set_flash('danger', 'Nama kategori wajib diisi.');
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO categories (name, description) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $name, $description);
        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'Tambah kategori', 'Menambahkan kategori ' . $name);
            set_flash('success', 'Kategori berhasil ditambahkan.');
            redirect('categories/index.php');
        }
        set_flash('danger', 'Gagal menambahkan kategori.');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h3>Form Tambah Kategori</h3>
        <a class="btn btn-light" href="<?= url('categories/index.php') ?>">Kembali</a>
    </div>

    <form method="POST" class="form-grid single">
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="name" required placeholder="Contoh: Kopi">
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" rows="4" placeholder="Deskripsi kategori"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

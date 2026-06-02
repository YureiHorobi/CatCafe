<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Tambah Meja';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = trim($_POST['table_number'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 4);
    $status = $_POST['status'] ?? 'available';

    if ($table_number === '') {
        set_flash('danger', 'Nomor meja wajib diisi.');
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO `tables` (table_number, capacity, status) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sis', $table_number, $capacity, $status);
        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'Tambah meja', 'Menambahkan meja ' . $table_number);
            set_flash('success', 'Meja berhasil ditambahkan.');
            redirect('tables/index.php');
        }
        set_flash('danger', 'Gagal menambahkan meja. Nomor meja mungkin sudah digunakan.');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h3>Form Tambah Meja</h3>
        <a class="btn btn-light" href="<?= url('tables/index.php') ?>">Kembali</a>
    </div>

    <form method="POST" class="form-grid single">
        <div class="form-group">
            <label>Nomor Meja</label>
            <input type="text" name="table_number" required placeholder="Contoh: T09">
        </div>
        <div class="form-group">
            <label>Kapasitas</label>
            <input type="number" name="capacity" min="1" value="4">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="reserved">Reserved</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Simpan</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

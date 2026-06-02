<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Edit Meja';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'SELECT * FROM `tables` WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$table = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$table) {
    set_flash('danger', 'Meja tidak ditemukan.');
    redirect('tables/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = trim($_POST['table_number'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 4);
    $status = $_POST['status'] ?? 'available';

    if ($table_number === '') {
        set_flash('danger', 'Nomor meja wajib diisi.');
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE `tables` SET table_number = ?, capacity = ?, status = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sisi', $table_number, $capacity, $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'Edit meja', 'Mengedit meja ' . $table_number);
            set_flash('success', 'Meja berhasil diperbarui.');
            redirect('tables/index.php');
        }
        set_flash('danger', 'Gagal memperbarui meja.');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h3>Form Edit Meja</h3>
        <a class="btn btn-light" href="<?= url('tables/index.php') ?>">Kembali</a>
    </div>

    <form method="POST" class="form-grid single">
        <div class="form-group">
            <label>Nomor Meja</label>
            <input type="text" name="table_number" required value="<?= h($table['table_number']) ?>">
        </div>
        <div class="form-group">
            <label>Kapasitas</label>
            <input type="number" name="capacity" min="1" value="<?= h($table['capacity']) ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="available" <?= $table['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="occupied" <?= $table['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                <option value="reserved" <?= $table['status'] === 'reserved' ? 'selected' : '' ?>>Reserved</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Update</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

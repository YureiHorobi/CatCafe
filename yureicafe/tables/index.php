<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Meja';

$status = trim($_GET['status'] ?? '');
if ($status !== '') {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM `tables` WHERE status = ? ORDER BY table_number ASC');
    mysqli_stmt_bind_param($stmt, 's', $status);
    mysqli_stmt_execute($stmt);
    $tables = mysqli_stmt_get_result($stmt);
} else {
    $tables = mysqli_query($conn, 'SELECT * FROM `tables` ORDER BY table_number ASC');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Data Meja</h3>
            <p class="muted">Kelola nomor meja dan status ketersediaan.</p>
        </div>
        <a href="<?= url('tables/create.php') ?>" class="btn btn-primary">+ Tambah Meja</a>
    </div>

    <form class="filter-bar" method="GET">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="occupied" <?= $status === 'occupied' ? 'selected' : '' ?>>Occupied</option>
            <option value="reserved" <?= $status === 'reserved' ? 'selected' : '' ?>>Reserved</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        <a href="<?= url('tables/index.php') ?>" class="btn btn-light">Reset</a>
    </form>

    <div class="table-card-grid">
        <?php if ($tables && mysqli_num_rows($tables) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($tables)): ?>
                <div class="table-card">
                    <div class="table-icon">🪑</div>
                    <h3>Meja <?= h($row['table_number']) ?></h3>
                    <p>Kapasitas <?= h($row['capacity']) ?> orang</p>
                    <?= status_badge($row['status']) ?>
                    <div class="actions center">
                        <a class="btn btn-sm btn-warning" href="<?= url('tables/edit.php?id=' . $row['id']) ?>">Edit</a>
                        <a class="btn btn-sm btn-danger" onclick="return confirmDelete()" href="<?= url('tables/delete.php?id=' . $row['id']) ?>">Hapus</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty">Data meja belum ada.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Order/Kasir';

$date = trim($_GET['date'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = 'SELECT o.*, t.table_number, u.fullname FROM orders o LEFT JOIN `tables` t ON o.table_id = t.id LEFT JOIN users u ON o.user_id = u.id WHERE 1=1';
$params = [];
$types = '';

if ($date !== '') {
    $sql .= ' AND DATE(o.created_at) = ?';
    $params[] = $date;
    $types .= 's';
}
if ($status !== '') {
    $sql .= ' AND o.status = ?';
    $params[] = $status;
    $types .= 's';
}
$sql .= ' ORDER BY o.created_at DESC';
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Data Order</h3>
            <p class="muted">Riwayat order dan transaksi Cat Cafe.</p>
        </div>
        <a href="<?= url('orders/create.php') ?>" class="btn btn-primary">+ Buat Order</a>
    </div>

    <form class="filter-bar" method="GET">
        <input type="date" name="date" value="<?= h($date) ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        <a href="<?= url('orders/index.php') ?>" class="btn btn-light">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No Order</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><strong><?= h($row['order_number']) ?></strong></td>
                    <td><?= h(date('d M Y H:i', strtotime($row['created_at']))) ?></td>
                    <td><?= h($row['customer_name'] ?: '-') ?></td>
                    <td><?= h($row['table_number'] ?: '-') ?></td>
                    <td><?= rupiah($row['grand_total']) ?></td>
                    <td><?= h(strtoupper($row['payment_method'])) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-secondary" href="<?= url('orders/detail.php?id=' . $row['id']) ?>">Detail</a>
                        <a class="btn btn-sm btn-primary" href="<?= url('orders/receipt.php?id=' . $row['id']) ?>">Struk</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="empty">Belum ada data order.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

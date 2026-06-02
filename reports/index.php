<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Laporan Penjualan';

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

$stmt = mysqli_prepare($conn, "SELECT o.*, t.table_number, u.fullname FROM orders o LEFT JOIN `tables` t ON o.table_id = t.id LEFT JOIN users u ON o.user_id = u.id WHERE DATE(o.created_at) BETWEEN ? AND ? ORDER BY o.created_at DESC");
mysqli_stmt_bind_param($stmt, 'ss', $start, $end);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total_orders, COALESCE(SUM(grand_total),0) AS revenue, COALESCE(AVG(grand_total),0) AS avg_order FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
mysqli_stmt_bind_param($stmt, 'ss', $start, $end);
mysqli_stmt_execute($stmt);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$daily = null;
$dailyCheck = mysqli_query($conn, "SHOW TABLES LIKE 'daily_sales_report'");
if ($dailyCheck && mysqli_num_rows($dailyCheck) > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM daily_sales_report WHERE sale_date BETWEEN ? AND ? ORDER BY sale_date DESC');
    mysqli_stmt_bind_param($stmt, 'ss', $start, $end);
    mysqli_stmt_execute($stmt);
    $daily = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="stats-grid report-stats">
    <div class="stat-card">
        <span>Total Transaksi</span>
        <strong><?= h($summary['total_orders']) ?></strong>
        <small>Order completed</small>
    </div>
    <div class="stat-card">
        <span>Total Pendapatan</span>
        <strong><?= rupiah($summary['revenue']) ?></strong>
        <small>Periode laporan</small>
    </div>
    <div class="stat-card">
        <span>Rata-rata Order</span>
        <strong><?= rupiah($summary['avg_order']) ?></strong>
        <small>Average order value</small>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Filter Laporan</h3>
            <p class="muted">Pilih periode tanggal penjualan.</p>
        </div>
        <button class="btn btn-primary" onclick="window.print()">Print Laporan</button>
    </div>
    <form class="filter-bar" method="GET">
        <input type="date" name="start" value="<?= h($start) ?>">
        <input type="date" name="end" value="<?= h($end) ?>">
        <button class="btn btn-secondary" type="submit">Tampilkan</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>Daftar Transaksi</h3>
        <span class="muted"><?= h(date('d M Y', strtotime($start))) ?> - <?= h(date('d M Y', strtotime($end))) ?></span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No Order</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?= h(date('d M Y H:i', strtotime($row['created_at']))) ?></td>
                    <td><?= h($row['order_number']) ?></td>
                    <td><?= h($row['customer_name'] ?: '-') ?></td>
                    <td><?= rupiah($row['grand_total']) ?></td>
                    <td><?= h(strtoupper($row['payment_method'])) ?></td>
                    <td><?= status_badge($row['status']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="empty">Tidak ada transaksi pada periode ini.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Ringkasan Harian</h3>
        <span class="muted">Menggunakan view daily_sales_report</span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Total Order</th>
                    <th>Pendapatan</th>
                    <th>Rata-rata</th>
                    <th>Cash</th>
                    <th>QRIS</th>
                    <th>Kartu</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($daily && mysqli_num_rows($daily) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($daily)): ?>
                <tr>
                    <td><?= h(date('d M Y', strtotime($row['sale_date']))) ?></td>
                    <td><?= h($row['total_orders']) ?></td>
                    <td><?= rupiah($row['total_revenue']) ?></td>
                    <td><?= rupiah($row['average_order_value']) ?></td>
                    <td><?= rupiah($row['cash_sales']) ?></td>
                    <td><?= rupiah($row['qris_sales']) ?></td>
                    <td><?= rupiah($row['card_sales']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="empty">Belum ada ringkasan harian.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Detail Order';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, 'SELECT o.*, t.table_number, u.fullname FROM orders o LEFT JOIN `tables` t ON o.table_id = t.id LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$order) {
    set_flash('danger', 'Order tidak ditemukan.');
    redirect('orders/index.php');
}

$stmt = mysqli_prepare($conn, 'SELECT od.*, p.name AS product_name FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$details = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Detail Order <?= h($order['order_number']) ?></h3>
            <p class="muted">Informasi order dan produk yang dibeli.</p>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="<?= url('orders/receipt.php?id=' . $order['id']) ?>">Lihat Struk</a>
            <a class="btn btn-light" href="<?= url('orders/index.php') ?>">Kembali</a>
        </div>
    </div>

    <div class="detail-grid">
        <div><span>No Order</span><strong><?= h($order['order_number']) ?></strong></div>
        <div><span>Tanggal</span><strong><?= h(date('d M Y H:i', strtotime($order['created_at']))) ?></strong></div>
        <div><span>Pelanggan</span><strong><?= h($order['customer_name'] ?: '-') ?></strong></div>
        <div><span>Kasir</span><strong><?= h($order['fullname'] ?: '-') ?></strong></div>
        <div><span>Jenis Order</span><strong><?= h(str_replace('_', ' ', ucwords($order['order_type']))) ?></strong></div>
        <div><span>Meja</span><strong><?= h($order['table_number'] ?: '-') ?></strong></div>
        <div><span>Pembayaran</span><strong><?= h(strtoupper($order['payment_method'])) ?></strong></div>
        <div><span>Status</span><strong><?= status_badge($order['status']) ?></strong></div>
    </div>

    <div class="table-responsive mt-20">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($details)): ?>
                <tr>
                    <td><?= h($row['product_name']) ?></td>
                    <td><?= rupiah($row['price']) ?></td>
                    <td><?= h($row['quantity']) ?></td>
                    <td><?= rupiah($row['subtotal']) ?></td>
                    <td><?= h($row['notes'] ?: '-') ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="summary-panel">
        <div><span>Total</span><strong><?= rupiah($order['total_amount']) ?></strong></div>
        <div><span>Diskon</span><strong><?= rupiah($order['discount']) ?></strong></div>
        <div><span>Pajak</span><strong><?= rupiah($order['tax']) ?></strong></div>
        <div class="grand"><span>Grand Total</span><strong><?= rupiah($order['grand_total']) ?></strong></div>
        <div><span>Bayar</span><strong><?= rupiah($order['payment_amount']) ?></strong></div>
        <div><span>Kembalian</span><strong><?= rupiah($order['change_amount']) ?></strong></div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

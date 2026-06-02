<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Struk Order';

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

<div class="receipt-actions no-print">
    <a class="btn btn-light" href="<?= url('orders/index.php') ?>">Kembali</a>
    <button class="btn btn-primary" onclick="window.print()">Print Struk</button>
</div>

<div class="receipt">
    <div class="receipt-head">
        <img class="receipt-logo-img" src="<?= url('assets/img/logo-cat-cafe.png') ?>" alt="Cat Cafe Logo">
        <h2>Cat Cafe</h2>
        <p>Jl. Contoh Cat Cafe No. 10</p>
        <p>Terima kasih sudah berbelanja</p>
    </div>

    <div class="receipt-info">
        <div><span>No</span><strong><?= h($order['order_number']) ?></strong></div>
        <div><span>Tanggal</span><strong><?= h(date('d/m/Y H:i', strtotime($order['created_at']))) ?></strong></div>
        <div><span>Kasir</span><strong><?= h($order['fullname'] ?: '-') ?></strong></div>
        <div><span>Pelanggan</span><strong><?= h($order['customer_name'] ?: '-') ?></strong></div>
        <div><span>Order</span><strong><?= h(str_replace('_', ' ', $order['order_type'])) ?></strong></div>
        <div><span>Meja</span><strong><?= h($order['table_number'] ?: '-') ?></strong></div>
    </div>

    <div class="receipt-line"></div>

    <table class="receipt-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($details)): ?>
            <tr>
                <td>
                    <?= h($row['product_name']) ?><br>
                    <small><?= rupiah($row['price']) ?></small>
                </td>
                <td><?= h($row['quantity']) ?></td>
                <td><?= rupiah($row['subtotal']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="receipt-line"></div>

    <div class="receipt-total">
        <div><span>Total</span><strong><?= rupiah($order['total_amount']) ?></strong></div>
        <div><span>Diskon</span><strong><?= rupiah($order['discount']) ?></strong></div>
        <div><span>Pajak</span><strong><?= rupiah($order['tax']) ?></strong></div>
        <div class="grand"><span>Grand Total</span><strong><?= rupiah($order['grand_total']) ?></strong></div>
        <div><span><?= h(strtoupper($order['payment_method'])) ?></span><strong><?= rupiah($order['payment_amount']) ?></strong></div>
        <div><span>Kembalian</span><strong><?= rupiah($order['change_amount']) ?></strong></div>
    </div>

    <p class="receipt-footer">Barang yang sudah dibeli tidak dapat dikembalikan.</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

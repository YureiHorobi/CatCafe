<?php
require_once __DIR__ . '/config/database.php';
require_login();

$pageTitle = 'Dashboard';

$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM products'))['total'] ?? 0;
$totalCategories = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM categories'))['total'] ?? 0;
$totalTables = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT COUNT(*) AS total FROM `tables`'))['total'] ?? 0;
$totalOrdersToday = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE DATE(created_at) = CURDATE()"))['total'] ?? 0;
$revenueToday = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(grand_total), 0) AS total FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'completed'"))['total'] ?? 0;

$lowStock = mysqli_query($conn, "SELECT name, stock, min_stock FROM products WHERE stock <= min_stock ORDER BY stock ASC LIMIT 5");
$latestOrders = mysqli_query($conn, "SELECT o.*, u.fullname FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 6");
$bestProducts = mysqli_query($conn, "SELECT * FROM best_selling_products LIMIT 5");

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <span>Total Produk</span>
        <strong><?= h($totalProducts) ?></strong>
        <small>Menu aktif dan tersimpan</small>
    </div>
    <div class="stat-card">
        <span>Total Kategori</span>
        <strong><?= h($totalCategories) ?></strong>
        <small>Kategori menu cafe</small>
    </div>
    <div class="stat-card">
        <span>Total Meja</span>
        <strong><?= h($totalTables) ?></strong>
        <small>Meja dine in</small>
    </div>
    <div class="stat-card">
        <span>Order Hari Ini</span>
        <strong><?= h($totalOrdersToday) ?></strong>
        <small><?= rupiah($revenueToday) ?></small>
    </div>
</div>

<div class="grid-two">
    <div class="card">
        <div class="card-header">
            <h3>Transaksi Terbaru</h3>
            <a href="<?= url('orders/index.php') ?>">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($latestOrders) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($latestOrders)): ?>
                    <tr>
                        <td><?= h($order['order_number']) ?></td>
                        <td><?= h($order['customer_name'] ?: '-') ?></td>
                        <td><?= rupiah($order['grand_total']) ?></td>
                        <td><?= status_badge($order['status']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="empty">Belum ada transaksi.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Stok Menipis</h3>
            <a href="<?= url('products/index.php') ?>">Kelola produk</a>
        </div>
        <div class="stack-list">
            <?php if (mysqli_num_rows($lowStock) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($lowStock)): ?>
                    <div class="stack-item">
                        <div>
                            <strong><?= h($item['name']) ?></strong>
                            <small>Minimal stok: <?= h($item['min_stock']) ?></small>
                        </div>
                        <span class="pill-warning">Stok <?= h($item['stock']) ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty">Semua stok masih aman.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Produk Terlaris</h3>
        <span class="muted">Berdasarkan view best_selling_products</span>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Total Terjual</th>
                    <th>Pendapatan</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($bestProducts && mysqli_num_rows($bestProducts) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($bestProducts)): ?>
                <tr>
                    <td><?= h($product['name']) ?></td>
                    <td><?= h($product['category']) ?></td>
                    <td><?= h($product['total_sold']) ?></td>
                    <td><?= rupiah($product['total_revenue']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty">Belum ada data produk terlaris.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

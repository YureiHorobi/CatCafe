<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Produk/Menu';

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1';
$params = [];
$types = '';

if ($search !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.barcode LIKE ? OR c.name LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($status !== '') {
    $sql .= ' AND p.status = ?';
    $params[] = $status;
    $types .= 's';
}

$sql .= ' ORDER BY p.created_at DESC';
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Data Produk/Menu</h3>
            <p class="muted">Kelola menu yang dijual di cafe.</p>
        </div>
        <a href="<?= url('products/create.php') ?>" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <form class="filter-bar" method="GET">
        <input type="text" name="search" placeholder="Cari nama, barcode, kategori..." value="<?= h($search) ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
            <option value="discontinued" <?= $status === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        <a href="<?= url('products/index.php') ?>" class="btn btn-light">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Barcode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($products && mysqli_num_rows($products) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td>
                        <div class="product-mini">
                            <div class="product-thumb"><?= $row['image'] ? '<img src="' . h(image_url($row['image'])) . '" alt="">' : '☕' ?></div>
                            <div>
                                <strong><?= h($row['name']) ?></strong>
                                <small><?= h($row['description'] ?: '-') ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= h($row['barcode'] ?: '-') ?></td>
                    <td><?= h($row['category_name'] ?: '-') ?></td>
                    <td><?= rupiah($row['price']) ?></td>
                    <td><?= h($row['stock']) ?> <small>/ min <?= h($row['min_stock']) ?></small></td>
                    <td><?= status_badge($row['status']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-warning" href="<?= url('products/edit.php?id=' . $row['id']) ?>">Edit</a>
                        <a class="btn btn-sm btn-danger" onclick="return confirmDelete()" href="<?= url('products/delete.php?id=' . $row['id']) ?>">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="empty">Data produk belum ada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

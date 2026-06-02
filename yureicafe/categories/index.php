<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Kategori';

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, 'SELECT * FROM categories WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC');
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $categories = mysqli_stmt_get_result($stmt);
} else {
    $categories = mysqli_query($conn, 'SELECT * FROM categories ORDER BY created_at DESC');
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Data Kategori</h3>
            <p class="muted">Kelola kategori produk cafe.</p>
        </div>
        <a href="<?= url('categories/create.php') ?>" class="btn btn-primary">+ Tambah Kategori</a>
    </div>

    <form class="filter-bar" method="GET">
        <input type="text" name="search" placeholder="Cari kategori..." value="<?= h($search) ?>">
        <button class="btn btn-secondary" type="submit">Cari</button>
        <a href="<?= url('categories/index.php') ?>" class="btn btn-light">Reset</a>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($categories && mysqli_num_rows($categories) > 0): ?>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= h($row['name']) ?></strong></td>
                    <td><?= h($row['description'] ?: '-') ?></td>
                    <td><?= h(date('d M Y', strtotime($row['created_at']))) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-warning" href="<?= url('categories/edit.php?id=' . $row['id']) ?>">Edit</a>
                        <a class="btn btn-sm btn-danger" onclick="return confirmDelete()" href="<?= url('categories/delete.php?id=' . $row['id']) ?>">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="empty">Data kategori belum ada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Tambah Produk';

$categories = mysqli_query($conn, 'SELECT id, name FROM categories ORDER BY name ASC');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $min_stock = (int) ($_POST['min_stock'] ?? 5);
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $status = $_POST['status'] ?? 'available';

    if ($name === '' || $price <= 0) {
        set_flash('danger', 'Nama produk dan harga wajib diisi dengan benar.');
    } else {
        $catValue = $category_id > 0 ? $category_id : null;
        $stmt = mysqli_prepare($conn, 'INSERT INTO products (barcode, name, category_id, price, stock, min_stock, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssidiisss', $barcode, $name, $catValue, $price, $stock, $min_stock, $description, $image, $status);
        if (mysqli_stmt_execute($stmt)) {
            log_activity($conn, 'Tambah produk', 'Menambahkan produk ' . $name);
            set_flash('success', 'Produk berhasil ditambahkan.');
            redirect('products/index.php');
        }
        set_flash('danger', 'Gagal menambahkan produk. Barcode mungkin sudah digunakan.');
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card form-card">
    <div class="card-header">
        <h3>Form Tambah Produk</h3>
        <a class="btn btn-light" href="<?= url('products/index.php') ?>">Kembali</a>
    </div>

    <form method="POST" class="form-grid">
        <div class="form-group">
            <label>Barcode</label>
            <input type="text" name="barcode" placeholder="Opsional">
        </div>
        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" required placeholder="Contoh: Cappuccino">
        </div>
        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id">
                <option value="">Pilih kategori</option>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?= h($cat['id']) ?>"><?= h($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Harga</label>
            <input type="number" name="price" min="0" required placeholder="25000">
        </div>
        <div class="form-group">
            <label>Stok</label>
            <input type="number" name="stock" min="0" value="0">
        </div>
        <div class="form-group">
            <label>Minimal Stok</label>
            <input type="number" name="min_stock" min="0" value="5">
        </div>
        <div class="form-group">
            <label>Link/Nama Gambar</label>
            <input type="text" name="image" placeholder="Opsional: assets/img/kopi.jpg">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="available">Available</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="discontinued">Discontinued</option>
            </select>
        </div>
        <div class="form-group span-2">
            <label>Deskripsi</label>
            <textarea name="description" rows="4"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Simpan Produk</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Buat Order';

$tables = mysqli_query($conn, "SELECT * FROM `tables` WHERE status IN ('available', 'reserved') ORDER BY table_number ASC");
$products = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'available' AND p.stock > 0 ORDER BY c.name, p.name");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_type = $_POST['order_type'] ?? 'dine_in';
    $table_id = $order_type === 'dine_in' && !empty($_POST['table_id']) ? (int) $_POST['table_id'] : null;
    $customer_name = trim($_POST['customer_name'] ?? '');
    $discount = (float) ($_POST['discount'] ?? 0);
    $tax = (float) ($_POST['tax'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $payment_amount = (float) ($_POST['payment_amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $items = json_decode($_POST['items_json'] ?? '[]', true);

    if (!is_array($items) || count($items) === 0) {
        $error = 'Keranjang masih kosong.';
    } else {
        mysqli_begin_transaction($conn);

        try {
            $total_amount = 0;
            $cleanItems = [];

            foreach ($items as $item) {
                $product_id = (int) ($item['id'] ?? 0);
                $qty = max(1, (int) ($item['qty'] ?? 1));

                $stmt = mysqli_prepare($conn, 'SELECT id, name, price, stock FROM products WHERE id = ? AND status = "available" LIMIT 1');
                mysqli_stmt_bind_param($stmt, 'i', $product_id);
                mysqli_stmt_execute($stmt);
                $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);

                if (!$product) {
                    throw new Exception('Produk tidak ditemukan.');
                }
                if ((int)$product['stock'] < $qty) {
                    throw new Exception('Stok produk ' . $product['name'] . ' tidak cukup.');
                }

                $price = (float) $product['price'];
                $subtotal = $price * $qty;
                $total_amount += $subtotal;
                $cleanItems[] = [
                    'product_id' => $product_id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'notes' => trim($item['notes'] ?? '')
                ];
            }

            $discount = max(0, min($discount, $total_amount));
            $tax = max(0, $tax);
            $grand_total = $total_amount - $discount + $tax;
            $change_amount = max(0, $payment_amount - $grand_total);
            $order_number = 'ORD' . date('YmdHis') . rand(10, 99);
            $user_id = (int) $_SESSION['user_id'];
            $status = 'completed';

            $stmt = mysqli_prepare($conn, 'INSERT INTO orders (order_number, table_id, user_id, customer_name, total_amount, discount, tax, grand_total, payment_method, payment_amount, change_amount, status, order_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'siisddddsddsss', $order_number, $table_id, $user_id, $customer_name, $total_amount, $discount, $tax, $grand_total, $payment_method, $payment_amount, $change_amount, $status, $order_type, $notes);
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            foreach ($cleanItems as $item) {
                $stmt = mysqli_prepare($conn, 'INSERT INTO order_details (order_id, product_id, quantity, price, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'iiidds', $order_id, $item['product_id'], $item['qty'], $item['price'], $item['subtotal'], $item['notes']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Memanggil stored procedure dari backend untuk mengurangi stok
            mysqli_next_result($conn);
            mysqli_query($conn, 'CALL update_stock_after_order(' . (int)$order_id . ')');
            while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}

            // Update ulang total supaya tetap sesuai setelah trigger menghitung total_amount.
            $stmt = mysqli_prepare($conn, 'UPDATE orders SET total_amount = ?, discount = ?, tax = ?, grand_total = ?, payment_amount = ?, change_amount = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'ddddddi', $total_amount, $discount, $tax, $grand_total, $payment_amount, $change_amount, $order_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_query($conn, "UPDATE products SET status = 'out_of_stock' WHERE stock <= 0");
            log_activity($conn, 'Buat order', 'Membuat order ' . $order_number);
            mysqli_commit($conn);

            set_flash('success', 'Order berhasil disimpan.');
            redirect('orders/receipt.php?id=' . $order_id);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= h($error) ?></div>
<?php endif; ?>

<form method="POST" id="orderForm" class="order-layout">
    <div class="card order-left">
        <div class="card-header">
            <div>
                <h3>Menu Cat Cafe</h3>
                <p class="muted">Klik produk untuk memasukkan ke keranjang.</p>
            </div>
            <input type="text" class="search-input" id="menuSearch" placeholder="Cari menu..." onkeyup="filterProductCards()">
        </div>

        <div class="product-grid" id="productGrid">
            <?php if ($products && mysqli_num_rows($products) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                    <button type="button" class="product-card" data-name="<?= h(strtolower($p['name'])) ?>" onclick="addToCart(<?= (int)$p['id'] ?>, '<?= h(addslashes($p['name'])) ?>', <?= (float)$p['price'] ?>, <?= (int)$p['stock'] ?>)">
                        <div class="product-card-img"><?= $p['image'] ? '<img src="' . h(image_url($p['image'])) . '" alt="">' : '☕' ?></div>
                        <strong><?= h($p['name']) ?></strong>
                        <span><?= h($p['category_name'] ?: 'Tanpa kategori') ?></span>
                        <small><?= rupiah($p['price']) ?> · Stok <?= h($p['stock']) ?></small>
                    </button>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty">Tidak ada produk tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card cart-card">
        <div class="card-header">
            <h3>Keranjang</h3>
            <button type="button" class="btn btn-light btn-sm" onclick="clearCart()">Kosongkan</button>
        </div>

        <div class="form-grid single compact">
            <div class="form-group">
                <label>Jenis Order</label>
                <select name="order_type" id="orderType" onchange="toggleTableSelect()">
                    <option value="dine_in">Dine In</option>
                    <option value="take_away">Take Away</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>
            <div class="form-group" id="tableSelectGroup">
                <label>Pilih Meja</label>
                <select name="table_id">
                    <option value="">Pilih meja</option>
                    <?php while ($t = mysqli_fetch_assoc($tables)): ?>
                        <option value="<?= h($t['id']) ?>"><?= h($t['table_number']) ?> - <?= h($t['capacity']) ?> orang</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="customer_name" placeholder="Opsional">
            </div>
        </div>

        <div class="cart-items" id="cartItems">
            <p class="empty">Keranjang masih kosong.</p>
        </div>

        <div class="payment-box">
            <div class="payment-row"><span>Total</span><strong id="displayTotal">Rp 0</strong></div>
            <div class="form-group">
                <label>Diskon</label>
                <input type="number" name="discount" id="discount" value="0" min="0" oninput="calculateCart()">
            </div>
            <div class="form-group">
                <label>Pajak</label>
                <input type="number" name="tax" id="tax" value="0" min="0" oninput="calculateCart()">
            </div>
            <div class="payment-row grand"><span>Grand Total</span><strong id="displayGrandTotal">Rp 0</strong></div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="payment_method">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </div>
            <div class="form-group">
                <label>Uang Bayar</label>
                <input type="number" name="payment_amount" id="paymentAmount" value="0" min="0" oninput="calculateCart()">
            </div>
            <div class="payment-row"><span>Kembalian</span><strong id="displayChange">Rp 0</strong></div>
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" rows="2" placeholder="Opsional"></textarea>
            </div>
        </div>

        <input type="hidden" name="items_json" id="itemsJson">
        <button type="submit" class="btn btn-primary btn-full" onclick="return prepareOrderSubmit()">Simpan Order</button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

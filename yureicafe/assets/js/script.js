let cart = [];

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('show');
}

function confirmDelete() {
    return confirm('Yakin ingin menghapus data ini?');
}

function formatRupiah(number) {
    number = Number(number || 0);
    return 'Rp ' + number.toLocaleString('id-ID');
}

function filterProductCards() {
    const keyword = (document.getElementById('menuSearch')?.value || '').toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.getAttribute('data-name') || '';
        card.style.display = name.includes(keyword) ? 'grid' : 'none';
    });
}

function addToCart(id, name, price, stock) {
    const found = cart.find(item => item.id === id);
    if (found) {
        if (found.qty >= stock) {
            alert('Stok tidak cukup.');
            return;
        }
        found.qty += 1;
    } else {
        cart.push({ id, name, price, stock, qty: 1, notes: '' });
    }
    renderCart();
}

function removeCartItem(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

function changeQty(id, change) {
    const item = cart.find(row => row.id === id);
    if (!item) return;

    const newQty = item.qty + change;
    if (newQty < 1) {
        removeCartItem(id);
        return;
    }
    if (newQty > item.stock) {
        alert('Jumlah melebihi stok tersedia.');
        return;
    }
    item.qty = newQty;
    renderCart();
}

function updateItemNote(id, value) {
    const item = cart.find(row => row.id === id);
    if (item) item.notes = value;
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p class="empty">Keranjang masih kosong.</p>';
    } else {
        container.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-top">
                    <div>
                        <strong>${escapeHtml(item.name)}</strong><br>
                        <small>${formatRupiah(item.price)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCartItem(${item.id})">Hapus</button>
                </div>
                <div class="cart-top">
                    <div class="qty-control">
                        <button type="button" onclick="changeQty(${item.id}, -1)">-</button>
                        <strong>${item.qty}</strong>
                        <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
                    </div>
                    <strong>${formatRupiah(item.qty * item.price)}</strong>
                </div>
                <input type="text" placeholder="Catatan item opsional" oninput="updateItemNote(${item.id}, this.value)">
            </div>
        `).join('');
    }

    calculateCart();
}

function calculateCart() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const discount = Number(document.getElementById('discount')?.value || 0);
    const tax = Number(document.getElementById('tax')?.value || 0);
    const grandTotal = Math.max(0, total - discount + tax);
    const paymentAmount = Number(document.getElementById('paymentAmount')?.value || 0);
    const change = Math.max(0, paymentAmount - grandTotal);

    const totalEl = document.getElementById('displayTotal');
    const grandEl = document.getElementById('displayGrandTotal');
    const changeEl = document.getElementById('displayChange');

    if (totalEl) totalEl.textContent = formatRupiah(total);
    if (grandEl) grandEl.textContent = formatRupiah(grandTotal);
    if (changeEl) changeEl.textContent = formatRupiah(change);
}

function prepareOrderSubmit() {
    if (cart.length === 0) {
        alert('Keranjang masih kosong.');
        return false;
    }

    const orderType = document.getElementById('orderType')?.value;
    const tableSelect = document.querySelector('[name="table_id"]');
    if (orderType === 'dine_in' && tableSelect && tableSelect.value === '') {
        alert('Pilih meja untuk order dine in.');
        return false;
    }

    const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const discount = Number(document.getElementById('discount')?.value || 0);
    const tax = Number(document.getElementById('tax')?.value || 0);
    const grandTotal = Math.max(0, total - discount + tax);
    const paymentAmount = Number(document.getElementById('paymentAmount')?.value || 0);

    if (paymentAmount < grandTotal) {
        return confirm('Uang bayar lebih kecil dari grand total. Tetap simpan order?');
    }

    const hidden = document.getElementById('itemsJson');
    if (hidden) hidden.value = JSON.stringify(cart);
    return true;
}

function toggleTableSelect() {
    const orderType = document.getElementById('orderType')?.value;
    const group = document.getElementById('tableSelectGroup');
    if (!group) return;
    group.style.display = orderType === 'dine_in' ? 'block' : 'none';
}

function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    toggleTableSelect();
    calculateCart();
});

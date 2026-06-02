Cat Cafe Frontend + PHP Native
================================

Project ini adalah frontend + halaman PHP Native Cat Cafe untuk backend database pos_cafe.
Dibuat sesuai struktur database yang sudah ada:
- users
- categories
- products
- tables
- orders
- order_details
- activity_logs
- view daily_sales_report
- view best_selling_products

Cara menjalankan di XAMPP:
1. Pastikan Apache dan MySQL di XAMPP aktif.
2. Import database backend pos_cafe.sql yang sudah kamu punya ke phpMyAdmin.
3. Ekstrak folder ini ke:
   C:/xampp/htdocs/pos-cafe-frontend
4. Buka browser:
   http://localhost/pos-cafe-frontend/
5. Login menggunakan:
   Admin: admin / admin123
   Kasir: kasir / kasir123

Catatan:
- File konfigurasi database ada di config/database.php.
- Jika username/password MySQL berbeda, ubah bagian:
  $db_user = 'root';
  $db_pass = '';
- Untuk gambar produk, field image bisa diisi link gambar atau path seperti assets/img/kopi.jpg.
- Project ini tidak memakai Laravel, Composer, React, atau Vue.
- Frontend dibuat sederhana agar mudah dipahami untuk tugas SMK.

Fitur:
1. Login admin/kasir
2. Dashboard statistik
3. CRUD kategori
4. CRUD produk/menu
5. CRUD meja
6. Halaman kasir/order dengan keranjang JavaScript
7. Simpan order ke tabel orders dan order_details
8. Memanggil stored procedure update_stock_after_order
9. Struk order + print
10. Laporan penjualan dengan filter tanggal

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <img class="brand-logo" src="<?= url('assets/img/logo-cat-cafe.png') ?>" alt="Cat Cafe Logo">
        <div>
            <h1>Cat Cafe</h1>
            <p>POS System</p>
        </div>
    </div>

    <nav class="side-nav">
        <a class="<?= nav_active('dashboard') ?>" href="<?= url('dashboard.php') ?>">🏠 Dashboard</a>
        <a class="<?= nav_active('products') ?>" href="<?= url('products/index.php') ?>">🍽️ Produk/Menu</a>
        <a class="<?= nav_active('categories') ?>" href="<?= url('categories/index.php') ?>">🏷️ Kategori</a>
        <a class="<?= nav_active('tables') ?>" href="<?= url('tables/index.php') ?>">🪑 Meja</a>
        <a class="<?= nav_active('orders') ?>" href="<?= url('orders/index.php') ?>">🧾 Order/Kasir</a>
        <a class="<?= nav_active('reports') ?>" href="<?= url('reports/index.php') ?>">📊 Laporan</a>
    </nav>

    <div class="sidebar-user">
        <strong><?= h(current_user_name()) ?></strong>
        <span><?= h(ucfirst(current_user_role())) ?></span>
        <a href="<?= url('logout.php') ?>" class="logout-link">Logout</a>
    </div>
</aside>
<main class="main-content">
    <header class="topbar">
        <button class="menu-toggle" type="button" onclick="toggleSidebar()">☰</button>
        <div>
            <h2><?= h($pageTitle) ?></h2>
            <p>Kelola operasional Cat Cafe dengan mudah</p>
        </div>
    </header>
    <section class="page-content">
        <?php flash_message(); ?>

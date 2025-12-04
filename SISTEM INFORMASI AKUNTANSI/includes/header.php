<?php
$currentUser = getCurrentUser();
// Determine base path
$basePath = '';
if (strpos($_SERVER['PHP_SELF'], '/akun/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/jurnal/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/transaksi/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/users/') !== false) {
    $basePath = '../';
}
?>
<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <h1>Sistem Informasi Akuntansi</h1>
        </div>
        <nav class="main-nav">
            <a href="<?php echo $basePath; ?>dashboard.php">Dashboard</a>
            <?php if (hasRole('Admin') || hasRole('Akuntan') || hasRole('Viewer')): ?>
            <a href="<?php echo $basePath; ?>akun/index.php">Akun</a>
            <a href="<?php echo $basePath; ?>jurnal/index.php">Jurnal</a>
            <a href="<?php echo $basePath; ?>transaksi/index.php">Transaksi</a>
            <?php endif; ?>
            <?php if (hasRole('Admin')): ?>
            <a href="<?php echo $basePath; ?>users/index.php">Users</a>
            <?php endif; ?>
        </nav>
        <div class="user-menu">
            <span><?php echo htmlspecialchars($currentUser['nama_lengkap']); ?> (<?php echo htmlspecialchars($currentUser['role_name']); ?>)</span>
            <a href="<?php echo $basePath; ?>logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </div>
</header>


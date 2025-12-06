<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$user = getCurrentUser();
$conn = getConnection();

// Get statistics
$stats = [];

// Total Akun
$result = $conn->query("SELECT COUNT(*) as total FROM akun");
$stats['total_akun'] = $result->fetch_assoc()['total'];

// Total Jurnal
$result = $conn->query("SELECT COUNT(*) as total FROM jurnal");
$stats['total_jurnal'] = $result->fetch_assoc()['total'];

// Total Transaksi
$result = $conn->query("SELECT COUNT(*) as total FROM transaksi");
$stats['total_transaksi'] = $result->fetch_assoc()['total'];

// Total Users (only for Admin)
if (hasRole('Admin')) {
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $result->fetch_assoc()['total'];
}

// Recent Jurnal (last 5)
$result = $conn->query("SELECT j.*, u.nama_lengkap FROM jurnal j 
                       LEFT JOIN users u ON j.user_id = u.id 
                       ORDER BY j.created_at DESC LIMIT 5");
$recent_jurnal = [];
while ($row = $result->fetch_assoc()) {
    $recent_jurnal[] = $row;
}

// Recent Transaksi (last 5)
$result = $conn->query("SELECT t.*, a.nama_akun, u.nama_lengkap FROM transaksi t 
                       LEFT JOIN akun a ON t.akun_id = a.id 
                       LEFT JOIN users u ON t.user_id = u.id 
                       ORDER BY t.created_at DESC LIMIT 5");
$recent_transaksi = [];
while ($row = $result->fetch_assoc()) {
    $recent_transaksi[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="Foto/accounting.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?>!</p>
        </div>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
            <div class="alert alert-error">Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman tersebut.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operasi berhasil dilakukan!</div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_akun']; ?></h3>
                    <p>Total Akun</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_jurnal']; ?></h3>
                    <p>Total Jurnal</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💼</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_transaksi']; ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
            
            <?php if (hasRole('Admin')): ?>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
                    <a href="akun/create.php" class="action-btn">
                        <span class="action-icon">➕</span>
                        <span>Tambah Akun</span>
                    </a>
                    <a href="jurnal/create.php" class="action-btn">
                        <span class="action-icon">📝</span>
                        <span>Tambah Jurnal</span>
                    </a>
                    <a href="transaksi/create.php" class="action-btn">
                        <span class="action-icon">💼</span>
                        <span>Tambah Transaksi</span>
                    </a>
                <?php endif; ?>
                <?php if (hasRole('Admin')): ?>
                    <a href="users/create.php" class="action-btn">
                        <span class="action-icon">👤</span>
                        <span>Tambah User</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Jurnal Terbaru</h2>
                <?php if (empty($recent_jurnal)): ?>
                    <p class="text-muted">Tidak ada jurnal</p>
                <?php else: ?>
                    <div class="recent-list">
                        <?php foreach ($recent_jurnal as $jurnal): ?>
                            <div class="recent-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($jurnal['no_jurnal']); ?></strong>
                                    <p class="text-muted"><?php echo date('d/m/Y', strtotime($jurnal['tanggal'])); ?> - <?php echo htmlspecialchars($jurnal['nama_lengkap']); ?></p>
                                </div>
                                <a href="jurnal/index.php" class="btn btn-sm btn-secondary">Lihat</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-card">
                <h2>Transaksi Terbaru</h2>
                <?php if (empty($recent_transaksi)): ?>
                    <p class="text-muted">Tidak ada transaksi</p>
                <?php else: ?>
                    <div class="recent-list">
                        <?php foreach ($recent_transaksi as $transaksi): ?>
                            <div class="recent-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($transaksi['no_transaksi']); ?></strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($transaksi['nama_akun']); ?> - Rp <?php echo number_format($transaksi['jumlah'], 2, ',', '.'); ?></p>
                                </div>
                                <a href="transaksi/index.php" class="btn btn-sm btn-secondary">Lihat</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>


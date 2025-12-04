<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

// Allow all logged in users to view (Viewer can only read)
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();
$search = $_GET['search'] ?? '';

$query = "SELECT t.*, a.kode_akun, a.nama_akun, u.nama_lengkap FROM transaksi t 
          LEFT JOIN akun a ON t.akun_id = a.id 
          LEFT JOIN users u ON t.user_id = u.id";
if (!empty($search)) {
    $query .= " WHERE t.no_transaksi LIKE '%$search%' OR t.keterangan LIKE '%$search%'";
}
$query .= " ORDER BY t.tanggal DESC, t.created_at DESC";

$result = $conn->query($query);
$transaksi_list = [];
while ($row = $result->fetch_assoc()) {
    $transaksi_list[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Transaksi - Sistem Informasi Akuntansi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Transaksi</h1>
            <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
            <a href="create.php" class="btn btn-primary">Tambah Transaksi</a>
            <?php endif; ?>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari no transaksi atau keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-link">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No Transaksi</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Akun</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksi_list as $transaksi): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaksi['no_transaksi']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($transaksi['tanggal'])); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($transaksi['jenis_transaksi']); ?>"><?php echo htmlspecialchars($transaksi['jenis_transaksi']); ?></span></td>
                                <td><?php echo htmlspecialchars($transaksi['kode_akun'] . ' - ' . $transaksi['nama_akun']); ?></td>
                                <td>Rp <?php echo number_format($transaksi['jumlah'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['keterangan']); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
                                <td>
                                    <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
                                    <a href="edit.php?id=<?php echo $transaksi['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?php echo $transaksi['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                    <?php else: ?>
                                    <span class="text-muted">Read-only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>


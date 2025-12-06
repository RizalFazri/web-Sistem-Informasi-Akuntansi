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

$query = "SELECT * FROM akun";
if (!empty($search)) {
    $query .= " WHERE kode_akun LIKE '%$search%' OR nama_akun LIKE '%$search%'";
}
$query .= " ORDER BY kode_akun";

$result = $conn->query($query);
$akun_list = [];
while ($row = $result->fetch_assoc()) {
    $akun_list[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Akun (Chart of Accounts)</h1>
            <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
            <a href="create.php" class="btn btn-primary">Tambah Akun</a>
            <?php endif; ?>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari kode atau nama akun..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Jenis Akun</th>
                        <th>Saldo Awal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($akun_list)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($akun_list as $akun): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($akun['kode_akun']); ?></td>
                                <td><?php echo htmlspecialchars($akun['nama_akun']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($akun['jenis_akun']); ?>"><?php echo htmlspecialchars($akun['jenis_akun']); ?></span></td>
                                <td>Rp <?php echo number_format($akun['saldo_awal'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
                                    <a href="edit.php?id=<?php echo $akun['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?php echo $akun['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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


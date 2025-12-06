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

$query = "SELECT j.*, u.nama_lengkap FROM jurnal j 
          LEFT JOIN users u ON j.user_id = u.id";
if (!empty($search)) {
    $query .= " WHERE j.no_jurnal LIKE '%$search%' OR j.keterangan LIKE '%$search%'";
}
$query .= " ORDER BY j.tanggal DESC, j.created_at DESC";

$result = $conn->query($query);
$jurnal_list = [];
while ($row = $result->fetch_assoc()) {
    // Get detail jurnal
    $stmt = $conn->prepare("SELECT dj.*, a.kode_akun, a.nama_akun FROM detail_jurnal dj 
                            JOIN akun a ON dj.akun_id = a.id 
                            WHERE dj.jurnal_id = ?");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $detail_result = $stmt->get_result();
    $row['details'] = [];
    while ($detail = $detail_result->fetch_assoc()) {
        $row['details'][] = $detail;
    }
    $stmt->close();
    $jurnal_list[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jurnal - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Daftar Jurnal Umum</h1>
            <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
            <a href="create.php" class="btn btn-primary">Tambah Jurnal</a>
            <?php endif; ?>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari no jurnal atau keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-link">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-container">
            <?php if (empty($jurnal_list)): ?>
                <div class="alert alert-info">Tidak ada data jurnal</div>
            <?php else: ?>
                <?php foreach ($jurnal_list as $jurnal): ?>
                    <div class="jurnal-card">
                        <div class="jurnal-header">
                            <div>
                                <h3><?php echo htmlspecialchars($jurnal['no_jurnal']); ?></h3>
                                <p class="text-muted">Tanggal: <?php echo date('d/m/Y', strtotime($jurnal['tanggal'])); ?> | Oleh: <?php echo htmlspecialchars($jurnal['nama_lengkap']); ?></p>
                            </div>
                            <div>
                                <?php if (hasRole('Admin') || hasRole('Akuntan')): ?>
                                <a href="edit.php?id=<?php echo $jurnal['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?php echo $jurnal['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                <?php else: ?>
                                <span class="text-muted">Read-only</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p><strong>Keterangan:</strong> <?php echo htmlspecialchars($jurnal['keterangan']); ?></p>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Akun</th>
                                    <th>Debit</th>
                                    <th>Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_debit = 0;
                                $total_kredit = 0;
                                foreach ($jurnal['details'] as $detail): 
                                    $total_debit += $detail['debit'];
                                    $total_kredit += $detail['kredit'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detail['kode_akun'] . ' - ' . $detail['nama_akun']); ?></td>
                                        <td><?php echo $detail['debit'] > 0 ? 'Rp ' . number_format($detail['debit'], 2, ',', '.') : '-'; ?></td>
                                        <td><?php echo $detail['kredit'] > 0 ? 'Rp ' . number_format($detail['kredit'], 2, ',', '.') : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td><strong>Total</strong></td>
                                    <td><strong>Rp <?php echo number_format($total_debit, 2, ',', '.'); ?></strong></td>
                                    <td><strong>Rp <?php echo number_format($total_kredit, 2, ',', '.'); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>


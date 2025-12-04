<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

if (!hasRole('Admin') && !hasRole('Akuntan')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$user = getCurrentUser();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_transaksi = $_POST['no_transaksi'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jenis_transaksi = $_POST['jenis_transaksi'] ?? '';
    $akun_id = $_POST['akun_id'] ?? 0;
    $jumlah = $_POST['jumlah'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($no_transaksi) || empty($tanggal) || empty($jenis_transaksi) || empty($akun_id) || $jumlah <= 0) {
        $error = 'Semua field harus diisi dan jumlah harus lebih dari 0!';
    } else {
        $conn = getConnection();
        
        // Check if no_transaksi already exists
        $stmt = $conn->prepare("SELECT id FROM transaksi WHERE no_transaksi = ?");
        $stmt->bind_param("s", $no_transaksi);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'No Transaksi sudah digunakan!';
        } else {
            $stmt = $conn->prepare("INSERT INTO transaksi (no_transaksi, tanggal, jenis_transaksi, akun_id, jumlah, keterangan, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssidsi", $no_transaksi, $tanggal, $jenis_transaksi, $akun_id, $jumlah, $keterangan, $user['id']);
            
            if ($stmt->execute()) {
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Gagal menambahkan transaksi: ' . $conn->error;
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Get akun list
$conn = getConnection();
$result = $conn->query("SELECT * FROM akun ORDER BY kode_akun");
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
    <title>Tambah Transaksi - Sistem Informasi Akuntansi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah Transaksi Baru</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="no_transaksi">No Transaksi *</label>
                        <input type="text" id="no_transaksi" name="no_transaksi" required placeholder="Contoh: TRX-001">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_transaksi">Jenis Transaksi *</label>
                        <select id="jenis_transaksi" name="jenis_transaksi" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Debit">Debit</option>
                            <option value="Kredit">Kredit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="akun_id">Akun *</label>
                        <select id="akun_id" name="akun_id" required>
                            <option value="">Pilih Akun</option>
                            <?php foreach ($akun_list as $akun): ?>
                                <option value="<?php echo $akun['id']; ?>"><?php echo htmlspecialchars($akun['kode_akun'] . ' - ' . $akun['nama_akun']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jumlah">Jumlah *</label>
                    <input type="number" id="jumlah" name="jumlah" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>


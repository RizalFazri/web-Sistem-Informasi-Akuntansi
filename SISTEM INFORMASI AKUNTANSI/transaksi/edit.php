<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

if (!hasRole('Admin') && !hasRole('Akuntan')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$id = $_GET['id'] ?? 0;
$error = '';

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM transaksi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();
$stmt->close();

if (!$transaksi) {
    header('Location: index.php');
    exit();
}

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
        // Check if no_transaksi already exists (excluding current)
        $stmt = $conn->prepare("SELECT id FROM transaksi WHERE no_transaksi = ? AND id != ?");
        $stmt->bind_param("si", $no_transaksi, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'No Transaksi sudah digunakan!';
        } else {
            $stmt = $conn->prepare("UPDATE transaksi SET no_transaksi = ?, tanggal = ?, jenis_transaksi = ?, akun_id = ?, jumlah = ?, keterangan = ? WHERE id = ?");
            $stmt->bind_param("sssidsi", $no_transaksi, $tanggal, $jenis_transaksi, $akun_id, $jumlah, $keterangan, $id);
            
            if ($stmt->execute()) {
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Gagal mengupdate transaksi: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Get akun list
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
    <title>Edit Transaksi - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Transaksi</h1>
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
                        <input type="text" id="no_transaksi" name="no_transaksi" required value="<?php echo htmlspecialchars($transaksi['no_transaksi']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" required value="<?php echo $transaksi['tanggal']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_transaksi">Jenis Transaksi *</label>
                        <select id="jenis_transaksi" name="jenis_transaksi" required>
                            <option value="Debit" <?php echo $transaksi['jenis_transaksi'] === 'Debit' ? 'selected' : ''; ?>>Debit</option>
                            <option value="Kredit" <?php echo $transaksi['jenis_transaksi'] === 'Kredit' ? 'selected' : ''; ?>>Kredit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="akun_id">Akun *</label>
                        <select id="akun_id" name="akun_id" required>
                            <option value="">Pilih Akun</option>
                            <?php foreach ($akun_list as $akun): ?>
                                <option value="<?php echo $akun['id']; ?>" <?php echo $akun['id'] == $transaksi['akun_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($akun['kode_akun'] . ' - ' . $akun['nama_akun']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jumlah">Jumlah *</label>
                    <input type="number" id="jumlah" name="jumlah" step="0.01" min="0.01" required value="<?php echo $transaksi['jumlah']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"><?php echo htmlspecialchars($transaksi['keterangan']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>


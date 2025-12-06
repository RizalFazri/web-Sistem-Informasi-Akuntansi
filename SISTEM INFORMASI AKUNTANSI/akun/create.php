<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

if (!hasRole('Admin') && !hasRole('Akuntan')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_akun = $_POST['kode_akun'] ?? '';
    $nama_akun = $_POST['nama_akun'] ?? '';
    $jenis_akun = $_POST['jenis_akun'] ?? '';
    $saldo_awal = $_POST['saldo_awal'] ?? 0;
    
    if (empty($kode_akun) || empty($nama_akun) || empty($jenis_akun)) {
        $error = 'Semua field harus diisi!';
    } else {
        $conn = getConnection();
        
        // Check if kode_akun already exists
        $stmt = $conn->prepare("SELECT id FROM akun WHERE kode_akun = ?");
        $stmt->bind_param("s", $kode_akun);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Kode akun sudah digunakan!';
        } else {
            $stmt = $conn->prepare("INSERT INTO akun (kode_akun, nama_akun, jenis_akun, saldo_awal) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $kode_akun, $nama_akun, $jenis_akun, $saldo_awal);
            
            if ($stmt->execute()) {
                $success = 'Akun berhasil ditambahkan!';
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Gagal menambahkan akun: ' . $conn->error;
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah Akun Baru</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="kode_akun">Kode Akun *</label>
                    <input type="text" id="kode_akun" name="kode_akun" required placeholder="Contoh: 1001">
                </div>
                
                <div class="form-group">
                    <label for="nama_akun">Nama Akun *</label>
                    <input type="text" id="nama_akun" name="nama_akun" required placeholder="Contoh: Kas">
                </div>
                
                <div class="form-group">
                    <label for="jenis_akun">Jenis Akun *</label>
                    <select id="jenis_akun" name="jenis_akun" required>
                        <option value="">Pilih Jenis Akun</option>
                        <option value="Aset">Aset</option>
                        <option value="Kewajiban">Kewajiban</option>
                        <option value="Ekuitas">Ekuitas</option>
                        <option value="Pendapatan">Pendapatan</option>
                        <option value="Beban">Beban</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="saldo_awal">Saldo Awal</label>
                    <input type="number" id="saldo_awal" name="saldo_awal" step="0.01" value="0" placeholder="0.00">
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


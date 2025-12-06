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
$stmt = $conn->prepare("SELECT * FROM akun WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$akun = $result->fetch_assoc();
$stmt->close();

if (!$akun) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_akun = $_POST['kode_akun'] ?? '';
    $nama_akun = $_POST['nama_akun'] ?? '';
    $jenis_akun = $_POST['jenis_akun'] ?? '';
    $saldo_awal = $_POST['saldo_awal'] ?? 0;
    
    if (empty($kode_akun) || empty($nama_akun) || empty($jenis_akun)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Check if kode_akun already exists (excluding current record)
        $stmt = $conn->prepare("SELECT id FROM akun WHERE kode_akun = ? AND id != ?");
        $stmt->bind_param("si", $kode_akun, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Kode akun sudah digunakan!';
        } else {
            $stmt = $conn->prepare("UPDATE akun SET kode_akun = ?, nama_akun = ?, jenis_akun = ?, saldo_awal = ? WHERE id = ?");
            $stmt->bind_param("sssdi", $kode_akun, $nama_akun, $jenis_akun, $saldo_awal, $id);
            
            if ($stmt->execute()) {
                header('Location: index.php?success=1');
                exit();
            } else {
                $error = 'Gagal mengupdate akun: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Akun</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="kode_akun">Kode Akun *</label>
                    <input type="text" id="kode_akun" name="kode_akun" required value="<?php echo htmlspecialchars($akun['kode_akun']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="nama_akun">Nama Akun *</label>
                    <input type="text" id="nama_akun" name="nama_akun" required value="<?php echo htmlspecialchars($akun['nama_akun']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="jenis_akun">Jenis Akun *</label>
                    <select id="jenis_akun" name="jenis_akun" required>
                        <option value="Aset" <?php echo $akun['jenis_akun'] === 'Aset' ? 'selected' : ''; ?>>Aset</option>
                        <option value="Kewajiban" <?php echo $akun['jenis_akun'] === 'Kewajiban' ? 'selected' : ''; ?>>Kewajiban</option>
                        <option value="Ekuitas" <?php echo $akun['jenis_akun'] === 'Ekuitas' ? 'selected' : ''; ?>>Ekuitas</option>
                        <option value="Pendapatan" <?php echo $akun['jenis_akun'] === 'Pendapatan' ? 'selected' : ''; ?>>Pendapatan</option>
                        <option value="Beban" <?php echo $akun['jenis_akun'] === 'Beban' ? 'selected' : ''; ?>>Beban</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="saldo_awal">Saldo Awal</label>
                    <input type="number" id="saldo_awal" name="saldo_awal" step="0.01" value="<?php echo $akun['saldo_awal']; ?>">
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


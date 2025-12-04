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
    $no_jurnal = $_POST['no_jurnal'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $akun_ids = $_POST['akun_id'] ?? [];
    $debits = $_POST['debit'] ?? [];
    $kredits = $_POST['kredit'] ?? [];
    
    if (empty($no_jurnal) || empty($tanggal)) {
        $error = 'No Jurnal dan Tanggal harus diisi!';
    } else {
        // Validate entries
        $total_debit = 0;
        $total_kredit = 0;
        $entries = [];
        
        for ($i = 0; $i < count($akun_ids); $i++) {
            if (!empty($akun_ids[$i])) {
                $debit = floatval($debits[$i] ?? 0);
                $kredit = floatval($kredits[$i] ?? 0);
                
                if ($debit > 0 || $kredit > 0) {
                    $entries[] = [
                        'akun_id' => $akun_ids[$i],
                        'debit' => $debit,
                        'kredit' => $kredit
                    ];
                    $total_debit += $debit;
                    $total_kredit += $kredit;
                }
            }
        }
        
        if (empty($entries)) {
            $error = 'Minimal harus ada satu entry!';
        } elseif (abs($total_debit - $total_kredit) > 0.01) {
            $error = 'Total debit dan kredit harus seimbang!';
        } else {
            $conn = getConnection();
            
            // Check if no_jurnal already exists
            $stmt = $conn->prepare("SELECT id FROM jurnal WHERE no_jurnal = ?");
            $stmt->bind_param("s", $no_jurnal);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'No Jurnal sudah digunakan!';
            } else {
                $conn->begin_transaction();
                
                try {
                    $stmt = $conn->prepare("INSERT INTO jurnal (no_jurnal, tanggal, keterangan, user_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $no_jurnal, $tanggal, $keterangan, $user['id']);
                    $stmt->execute();
                    $jurnal_id = $conn->insert_id;
                    $stmt->close();
                    
                    $stmt = $conn->prepare("INSERT INTO detail_jurnal (jurnal_id, akun_id, debit, kredit) VALUES (?, ?, ?, ?)");
                    foreach ($entries as $entry) {
                        $stmt->bind_param("iidd", $jurnal_id, $entry['akun_id'], $entry['debit'], $entry['kredit']);
                        $stmt->execute();
                    }
                    $stmt->close();
                    
                    $conn->commit();
                    header('Location: index.php?success=1');
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Gagal menyimpan jurnal: ' . $e->getMessage();
                }
            }
            
            $conn->close();
        }
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
    <title>Tambah Jurnal - Sistem Informasi Akuntansi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Tambah Jurnal Baru</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="" id="jurnalForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="no_jurnal">No Jurnal *</label>
                        <input type="text" id="no_jurnal" name="no_jurnal" required placeholder="Contoh: JUR-001">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"></textarea>
                </div>
                
                <h3>Detail Jurnal</h3>
                <div id="entries-container">
                    <div class="entry-row">
                        <div class="form-group">
                            <label>Akun</label>
                            <select name="akun_id[]" class="akun-select" required>
                                <option value="">Pilih Akun</option>
                                <?php foreach ($akun_list as $akun): ?>
                                    <option value="<?php echo $akun['id']; ?>"><?php echo htmlspecialchars($akun['kode_akun'] . ' - ' . $akun['nama_akun']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Debit</label>
                            <input type="number" name="debit[]" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label>Kredit</label>
                            <input type="number" name="kredit[]" step="0.01" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-danger btn-sm remove-entry">Hapus</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="add-entry">Tambah Entry</button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('add-entry').addEventListener('click', function() {
            const container = document.getElementById('entries-container');
            const newRow = container.firstElementChild.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelector('select').value = '';
            container.appendChild(newRow);
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-entry')) {
                if (document.querySelectorAll('.entry-row').length > 1) {
                    e.target.closest('.entry-row').remove();
                } else {
                    alert('Minimal harus ada satu entry!');
                }
            }
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>


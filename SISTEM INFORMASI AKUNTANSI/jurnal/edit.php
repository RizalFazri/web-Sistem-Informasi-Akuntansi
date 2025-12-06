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
$stmt = $conn->prepare("SELECT * FROM jurnal WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$jurnal = $result->fetch_assoc();
$stmt->close();

if (!$jurnal) {
    header('Location: index.php');
    exit();
}

// Get detail jurnal
$stmt = $conn->prepare("SELECT * FROM detail_jurnal WHERE jurnal_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$detail_result = $stmt->get_result();
$details = [];
while ($detail = $detail_result->fetch_assoc()) {
    $details[] = $detail;
}
$stmt->close();

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
            // Check if no_jurnal already exists (excluding current)
            $stmt = $conn->prepare("SELECT id FROM jurnal WHERE no_jurnal = ? AND id != ?");
            $stmt->bind_param("si", $no_jurnal, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'No Jurnal sudah digunakan!';
            } else {
                $conn->begin_transaction();
                
                try {
                    $stmt = $conn->prepare("UPDATE jurnal SET no_jurnal = ?, tanggal = ?, keterangan = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $no_jurnal, $tanggal, $keterangan, $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Delete old details
                    $stmt = $conn->prepare("DELETE FROM detail_jurnal WHERE jurnal_id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Insert new details
                    $stmt = $conn->prepare("INSERT INTO detail_jurnal (jurnal_id, akun_id, debit, kredit) VALUES (?, ?, ?, ?)");
                    foreach ($entries as $entry) {
                        $stmt->bind_param("iidd", $id, $entry['akun_id'], $entry['debit'], $entry['kredit']);
                        $stmt->execute();
                    }
                    $stmt->close();
                    
                    $conn->commit();
                    header('Location: index.php?success=1');
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Gagal mengupdate jurnal: ' . $e->getMessage();
                }
            }
        }
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
    <title>Edit Jurnal - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Jurnal</h1>
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
                        <input type="text" id="no_jurnal" name="no_jurnal" required value="<?php echo htmlspecialchars($jurnal['no_jurnal']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal" required value="<?php echo $jurnal['tanggal']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"><?php echo htmlspecialchars($jurnal['keterangan']); ?></textarea>
                </div>
                
                <h3>Detail Jurnal</h3>
                <div id="entries-container">
                    <?php foreach ($details as $detail): ?>
                        <div class="entry-row">
                            <div class="form-group">
                                <label>Akun</label>
                                <select name="akun_id[]" class="akun-select" required>
                                    <option value="">Pilih Akun</option>
                                    <?php foreach ($akun_list as $akun): ?>
                                        <option value="<?php echo $akun['id']; ?>" <?php echo $akun['id'] == $detail['akun_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($akun['kode_akun'] . ' - ' . $akun['nama_akun']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Debit</label>
                                <input type="number" name="debit[]" step="0.01" min="0" value="<?php echo $detail['debit']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Kredit</label>
                                <input type="number" name="kredit[]" step="0.01" min="0" value="<?php echo $detail['kredit']; ?>">
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-danger btn-sm remove-entry">Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="add-entry">Tambah Entry</button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Jurnal</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('add-entry').addEventListener('click', function() {
            const container = document.getElementById('entries-container');
            const newRow = container.firstElementChild.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => {
                if (input.type === 'number') input.value = '0';
            });
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


<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

// Only Admin can access user management
if (!hasRole('Admin')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$conn = getConnection();
$search = $_GET['search'] ?? '';

$query = "SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id";
if (!empty($search)) {
    $query .= " WHERE u.username LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%' OR r.nama_role LIKE '%$search%'";
}
$query .= " ORDER BY u.created_at DESC";

$result = $conn->query($query);
$users_list = [];
while ($row = $result->fetch_assoc()) {
    $users_list[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Users - Sistem Informasi Akuntansi</title>
    <link rel="icon" type="image/png" href="../Foto/accounting.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Manajemen Users</h1>
            <a href="create.php" class="btn btn-primary">Tambah User</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">User berhasil <?php echo $_GET['success'] === 'created' ? 'ditambahkan' : ($_GET['success'] === 'updated' ? 'diupdate' : 'dihapus'); ?>!</div>
        <?php endif; ?>
        
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari username, nama, atau role..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users_list as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($user['nama_role']); ?></span></td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                                    <?php else: ?>
                                        <span class="text-muted">(Anda)</span>
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


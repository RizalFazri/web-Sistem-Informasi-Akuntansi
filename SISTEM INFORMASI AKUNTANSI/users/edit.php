<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

// Only Admin can access user management
if (!hasRole('Admin')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$id = $_GET['id'] ?? 0;
$error = '';

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $role_id = $_POST['role_id'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($username) || empty($nama_lengkap) || empty($role_id)) {
        $error = 'Username, Nama Lengkap, dan Role harus diisi!';
    } else {
        // Check if username already exists (excluding current record)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, nama_lengkap = ?, email = ?, role_id = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssssiii", $username, $hashed_password, $nama_lengkap, $email, $role_id, $is_active, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, nama_lengkap = ?, email = ?, role_id = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $username, $nama_lengkap, $email, $role_id, $is_active, $id);
            }
            
            if ($stmt->execute()) {
                header('Location: index.php?success=updated');
                exit();
            } else {
                $error = 'Gagal mengupdate user: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}

// Get roles list
$result = $conn->query("SELECT * FROM roles ORDER BY nama_role");
$roles_list = [];
while ($row = $result->fetch_assoc()) {
    $roles_list[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Sistem Informasi Akuntansi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit User</h1>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password baru">
                </div>
                
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="role_id">Role *</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">Pilih Role</option>
                        <?php foreach ($roles_list as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['nama_role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>> Aktif
                    </label>
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


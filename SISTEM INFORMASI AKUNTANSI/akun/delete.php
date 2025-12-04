<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

if (!hasRole('Admin') && !hasRole('Akuntan')) {
    header('Location: ../dashboard.php?error=access_denied');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $conn = getConnection();
    
    // Check if akun is used in transactions
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transaksi WHERE akun_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        header('Location: index.php?error=Akun tidak dapat dihapus karena masih digunakan dalam transaksi');
        exit();
    }
    
    // Check if akun is used in journal entries
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM detail_jurnal WHERE akun_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    if ($count > 0) {
        header('Location: index.php?error=Akun tidak dapat dihapus karena masih digunakan dalam jurnal');
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM akun WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    header('Location: index.php?success=1');
} else {
    header('Location: index.php');
}
exit();
?>


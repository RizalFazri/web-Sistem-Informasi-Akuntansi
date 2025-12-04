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
    
    // Delete detail_jurnal first (CASCADE should handle this, but being explicit)
    $stmt = $conn->prepare("DELETE FROM detail_jurnal WHERE jurnal_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Delete jurnal
    $stmt = $conn->prepare("DELETE FROM jurnal WHERE id = ?");
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


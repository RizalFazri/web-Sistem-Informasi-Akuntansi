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

if ($id == $_SESSION['user_id']) {
    header('Location: index.php?error=cannot_delete_self');
    exit();
}

$conn = getConnection();

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    header('Location: index.php');
    exit();
}
$stmt->close();

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: index.php?success=deleted');
} else {
    header('Location: index.php?error=delete_failed');
}

$stmt->close();
$conn->close();
exit();
?>


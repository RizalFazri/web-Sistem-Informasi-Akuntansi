<?php
// Script untuk update password di database
// Akses via browser: http://localhost/SISTEM INFORMASI AKUNTANSI/update_password.php

require_once 'config/database.php';

// Generate hash untuk password "admin123"
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Update Password Hash</h2>";
echo "<p>Password: admin123</p>";
echo "<p>Hash: " . $hash . "</p>";

// Update password di database
$conn = getConnection();

// Update untuk semua user
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username IN ('admin', 'akuntan1', 'viewer1')");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "<p style='color: green;'><strong>Password berhasil diupdate!</strong></p>";
    echo "<p>Silakan login dengan:</p>";
    echo "<ul>";
    echo "<li>Username: admin, Password: admin123</li>";
    echo "<li>Username: akuntan1, Password: admin123</li>";
    echo "<li>Username: viewer1, Password: admin123</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();

echo "<p><a href='login.php'>Kembali ke Login</a></p>";
?>


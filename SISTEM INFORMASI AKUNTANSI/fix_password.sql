-- SQL untuk memperbaiki password hash di database
-- Jalankan query ini di phpMyAdmin jika login gagal
-- Password untuk semua user: admin123

-- Update password untuk admin
UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' WHERE username = 'admin';

-- Update password untuk akuntan1
UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' WHERE username = 'akuntan1';

-- Update password untuk viewer1
UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' WHERE username = 'viewer1';


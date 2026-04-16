<?php
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Delete old admin if exists
$db->prepare("DELETE FROM users WHERE email = 'admin@carsales.com'")->execute();

// Create fresh admin with properly hashed password
$hash = password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost' => 10]);

$stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute(['Admin', 'admin@carsales.com', $hash]);

echo "✅ Admin created! Login with: admin@carsales.com / Admin1234!";


?>
<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
$pdo->query("USE shabakat_sama");

// Set password for the first user
$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
$stmt->execute([$hash]);
echo "Password set for user 1 (phone: 777111222, password: 'password')\n";

// Verify
$stmt = $pdo->query("SELECT id, name, phone FROM users WHERE id = 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($user, JSON_UNESCAPED_UNICODE) . "\n";
echo "Hash: " . substr($hash, 0, 30) . "...\n";
echo "Verify 'password': " . (password_verify('password', $hash) ? 'OK' : 'FAIL') . "\n";

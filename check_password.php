<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
$pdo->query("USE shabakat_sama");

$stmt = $pdo->query("SELECT id, name, phone, password FROM users WHERE phone = '777211694'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User: " . json_encode(['name' => $user['name'], 'phone' => $user['phone']], JSON_UNESCAPED_UNICODE) . "\n";
echo "Password hash: " . $user['password'] . "\n";
echo "Verify 'password': " . (password_verify('password', $user['password']) ? 'MATCH' : 'NO MATCH') . "\n";
echo "Verify '123456': " . (password_verify('123456', $user['password']) ? 'MATCH' : 'NO MATCH') . "\n";
echo "Verify '777211694': " . (password_verify('777211694', $user['password']) ? 'MATCH' : 'NO MATCH') . "\n";

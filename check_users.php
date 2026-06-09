<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
$pdo->query("USE shabakat_sama");

$stmt = $pdo->query("SELECT id, name, phone FROM users");
echo "=== Users in shabakat_sama ===\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  ID: {$row['id']}, Name: {$row['name']}, Phone: {$row['phone']}\n";
}

// Check if any password is a known hash
$stmt = $pdo->query("SELECT id, name, phone, password FROM users LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nFirst user password hash: " . substr($row['password'], 0, 30) . "...\n";
echo "Uses bcrypt: " . (strpos($row['password'], '$2y$') === 0 ? 'YES' : 'NO') . "\n";

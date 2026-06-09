<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');

foreach (['shabakat_rewards', 'shabakat_sama'] as $db) {
    $pdo->query("USE $db");
    $stmt = $pdo->query("SELECT id, name, phone, LENGTH(password) as pw_len FROM users");
    echo "=== $db ===\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  ID: {$row['id']}, Name: {$row['name']}, Phone: {$row['phone']}, Password length: {$row['pw_len']}\n";
    }
}

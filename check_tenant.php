<?php
$host = '127.0.0.1';
$db = 'shabakat_central';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    echo "=== TENANTS ===\n";
    $stmt = $pdo->query("SELECT * FROM tenants");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n=== DOMAINS ===\n";
    $stmt = $pdo->query("SELECT * FROM domains");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n=== ADMINS ===\n";
    $stmt = $pdo->query("SELECT id, email, role, tenant_id FROM admins");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

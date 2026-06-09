<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');

$pdo->query("USE shabakat_sama");

echo "=== USERS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as c FROM users");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Count: " . $r['c'] . "\n";
$stmt = $pdo->query("SELECT id, name, phone, points_balance FROM users LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== REWARD_CARDS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as c FROM reward_cards");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Count: " . $r['c'] . "\n";
$stmt = $pdo->query("SELECT id, status FROM reward_cards LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r) . "\n";
}

echo "\n=== POINT_TRANSACTIONS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as c FROM point_transactions");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Count: " . $r['c'] . "\n";

echo "\n=== REWARDS ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as c FROM rewards");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Count: " . $r['c'] . "\n";
$stmt = $pdo->query("SELECT id, name, card_value, points_cost, is_active FROM rewards LIMIT 5");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

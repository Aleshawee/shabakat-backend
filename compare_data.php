<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');

$databases = ['shabakat_rewards', 'shabakat_sama'];

foreach ($databases as $db) {
    echo "\n=== $db ===\n";
    $pdo->query("USE $db");
    
    $tables = ['users', 'reward_cards', 'rewards', 'point_transactions', 'categories', 'banners', 'network_cards', 'notifications', 'card_redemptions', 'otp_codes', 'settings', 'sms_messages', 'absher_settings', 'transfer_settings', 'feature_toggles', 'restrictions'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM $table");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "  $table: " . $row['c'] . "\n";
        } catch (PDOException $e) {
            echo "  $table: TABLE NOT FOUND\n";
        }
    }
}

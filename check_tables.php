<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');

$stmt = $pdo->query("SHOW DATABASES LIKE 'shabakat_%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $keys = array_keys($row);
    $db = $row[$keys[0]];
    echo "\n=== $db ===\n";
    $pdo->query("USE $db");
    $tables = $pdo->query("SHOW TABLES");
    while ($t = $tables->fetch(PDO::FETCH_ASSOC)) {
        $tkeys = array_keys($t);
        echo "  - " . $t[$tkeys[0]] . "\n";
    }
}

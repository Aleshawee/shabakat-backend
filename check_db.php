<?php
$pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '');
$stmt = $pdo->query("SHOW DATABASES LIKE 'shabakat_%'");
echo "Databases:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $keys = array_keys($row);
    echo " - " . $row[$keys[0]] . "\n";
}

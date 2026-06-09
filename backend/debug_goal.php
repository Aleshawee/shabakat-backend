<?php
// محاكاة goal4.php بالضبط
$apiKey = "79f44f7c21mshf695050f701b10dp167070jsn7a30d151ff9b";
$selectedDate = '2026-05-24';
$apiDate = date('Ymd', strtotime($selectedDate));

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://livescore6.p.rapidapi.com/matches/v2/list-by-date?Category=soccer&Date=" . $apiDate,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["x-rapidapi-host: livescore6.p.rapidapi.com", "x-rapidapi-key: " . $apiKey],
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
$stages = $data['Stages'] ?? [];

foreach ($stages as $stage) {
    $sn = $stage['Snm'] ?? '';
    if (stripos($sn, 'premier') === false) continue;
    foreach (($stage['Events'] ?? []) as $match) {
        $esd = $match['Esd'] ?? 'N/A';
        $t1 = $match['T1'][0]['Nm'] ?? '?';
        $t2 = $match['T2'][0]['Nm'] ?? '?';
        echo "Match: $t1 vs $t2 | Raw Esd: $esd | ";
        if (strlen($esd) >= 12) {
            $dateObj = DateTime::createFromFormat('YmdHis', $esd, new DateTimeZone('UTC'));
            echo "UTC: " . $dateObj->format('H:i') . " | ";
            $dateObj->setTimezone(new DateTimeZone('Asia/Aden'));
            echo "Aden: " . $dateObj->format('H:i') . " | ";
            // إذا كان Esd أصلاً بتوقيت محلي
            $localObj = DateTime::createFromFormat('YmdHis', $esd, new DateTimeZone('Asia/Aden'));
            echo "LocalRaw: " . $localObj->format('H:i');
        } else {
            echo "Cannot parse";
        }
        echo PHP_EOL;
    }
}

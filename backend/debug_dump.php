<?php
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://livescore6.p.rapidapi.com/matches/v2/list-by-date?Category=soccer&Date=20260524",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["x-rapidapi-host: livescore6.p.rapidapi.com", "x-rapidapi-key: 79f44f7c21mshf695050f701b10dp167070jsn7a30d151ff9b"],
]);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
foreach ($data['Stages'] ?? [] as $s) {
    if (stripos($s['Snm'] ?? '', 'premier league') === false || ($s['Cnm'] ?? '') !== 'England') continue;
    foreach (($s['Events'] ?? []) as $e) {
        echo json_encode($e, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break 2;
    }
}

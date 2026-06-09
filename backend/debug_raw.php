<?php
// محاكاة goal4.php وأخذ Esd الخام
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://livescore6.p.rapidapi.com/matches/v2/list-by-date?Category=soccer&Date=20260524",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["x-rapidapi-host: livescore6.p.rapidapi.com", "x-rapidapi-key: 79f44f7c21mshf695050f701b10dp167070jsn7a30d151ff9b"],
]);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
$stages = $data['Stages'] ?? [];
foreach ($stages as $s) {
    $sn = $s['Snm'] ?? '';
    if (stripos($sn, 'premier') === false) continue;
    foreach (($s['Events'] ?? []) as $e) {
        $t1 = $e['T1'][0]['Nm'] ?? '?';
        $t2 = $e['T2'][0]['Nm'] ?? '?';
        $esd = $e['Esd'] ?? 'N/A';
        echo "$t1 vs $t2 | Esd=$esd | ";
        // بدون تحويل، نعرض Esd مباشرة كـ H:i
        echo "RawH=" . substr($esd, 8, 2) . ":" . substr($esd, 10, 2) . PHP_EOL;
    }
}

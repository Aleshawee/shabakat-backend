<?php
// Simulate: no conversion, raw Esd
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://livescore6.p.rapidapi.com/matches/v2/list-by-date?Category=soccer&Date=20260524",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["x-rapidapi-host: livescore6.p.rapidapi.com", "x-rapidapi-key: 79f44f7c21mshf695050f701b10dp167070jsn7a30d151ff9b"],
]);
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);

$selectedDate = '2026-05-24';
foreach ($data['Stages'] ?? [] as $s) {
    $sn = $s['Snm'] ?? '';
    $cn = $s['Cnm'] ?? '';
    $lower = mb_strtolower($sn);
    $excluded = false;
    foreach (['women','u19','u20','u21','championship','league 1','league 2','serie b','segunda','laliga2'] as $p) {
        if (str_contains($lower, $p)) { $excluded = true; break; }
    }
    if ($excluded) continue;
    $cl = strtolower(trim($cn));
    $countryOk = ($cl !== '' && (str_contains($cl, 'england') || str_contains($cl, 'spain') || str_contains($cl, 'italy') || str_contains($cl, 'germany') || str_contains($cl, 'france')));
    if ($cl === '') {
        foreach (['premier league','laliga','serie a','bundesliga','ligue 1'] as $k) {
            if (str_contains($lower, $k)) { $countryOk = true; break; }
        }
    }
    if (!$countryOk) continue;

    $isMajor = ($cn === '' && (str_contains($lower, 'champions league') || str_contains($lower, 'world cup')));

    $allowed = ['Manchester City','Manchester City FC','Man City','Manchester Utd','Manchester United','Manchester United FC','Man Utd','Chelsea','Chelsea FC','Arsenal','Arsenal FC','Liverpool','Liverpool FC','Real Madrid','Real Madrid CF','FC Barcelona','Barcelona','Atletico Madrid','Atletico de Madrid','Bayern Munich','Bayern Munchen','FC Bayern','FC Bayern Munich','PSG','Paris SG','Paris Saint-Germain','Paris Saint Germain','Inter','Inter Milan','FC Internazionale','Internazionale','AC Milan','Milan','AC Milan FC','Juventus','Juventus FC','Napoli','SSC Napoli','Atalanta','Atalanta BC'];

    $hasAny = false;
    foreach (($s['Events'] ?? []) as $e) {
        $t1 = trim($e['T1'][0]['Nm'] ?? '');
        $t2 = trim($e['T2'][0]['Nm'] ?? '');
        $teamOk = $isMajor;
        if (!$isMajor) {
            $t1l = mb_strtolower($t1); $t2l = mb_strtolower($t2);
            foreach ($allowed as $at) {
                $atl = mb_strtolower(trim($at));
                if ($t1l === $atl || $t2l === $atl) { $teamOk = true; break; }
            }
        }
        if (!$teamOk) continue;

        $esd = $e['Esd'] ?? '';
        if (strlen($esd) >= 12) {
            $h = substr($esd, 8, 2);
            $m = substr($esd, 10, 2);
            $d = substr($esd, 0, 4) . '-' . substr($esd, 4, 2) . '-' . substr($esd, 6, 2);
            if ($d !== $selectedDate) continue;
            if (!$hasAny) {
                echo "=== $sn ($cn) ===" . PHP_EOL;
                $hasAny = true;
            }
            echo "  $t1 vs $t2 @ $h:$m" . PHP_EOL;
        }
    }
}

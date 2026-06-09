<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MatchApiController extends Controller
{
    private $apiKey = '79f44f7c21mshf695050f701b10dp167070jsn7a30d151ff9b';
    private $apiHost = 'livescore6.p.rapidapi.com';

    private $allowedTeams = [
        'Real Madrid', 'Real Madrid CF', 'FC Barcelona', 'Barcelona',
        'Atletico Madrid', 'Atletico de Madrid',
        'Manchester City', 'Manchester City FC', 'Man City',
        'Manchester Utd', 'Manchester United', 'Manchester United FC', 'Man Utd',
        'Chelsea', 'Chelsea FC',
        'Arsenal', 'Arsenal FC',
        'Liverpool', 'Liverpool FC',
        'Bayern Munich', 'Bayern München', 'FC Bayern', 'FC Bayern Munich',
        'PSG', 'Paris SG', 'Paris Saint-Germain', 'Paris Saint Germain',
        'Inter', 'Inter Milan', 'FC Internazionale', 'Internazionale',
        'AC Milan', 'Milan', 'AC Milan FC',
        'Juventus', 'Juventus FC',
        'Napoli', 'SSC Napoli',
        'Atalanta', 'Atalanta BC',
    ];

    private $arabicDictionary = [
        // Countries
        'England' => 'إنجلترا', 'Spain' => 'إسبانيا', 'Italy' => 'إيطاليا',
        'Germany' => 'ألمانيا', 'France' => 'فرنسا',
        'Saudi Arabia' => 'السعودية',

        // Leagues
        'Premier League' => 'الدوري الإنجليزي الممتاز',
        'LaLiga' => 'الدوري الإسباني',
        'Serie A' => 'الدوري الإيطالي',
        'Bundesliga' => 'الدوري الألماني',
        'Ligue 1' => 'الدوري الفرنسي',
        'Champions League' => 'دوري أبطال أوروبا',
        'Europa League' => 'الدوري الأوروبي',
        'World Cup' => 'كأس العالم',

        // England
        'Manchester City' => 'مانشستر سيتي',
        'Manchester Utd' => 'مانشستر يونايتد', 'Manchester United' => 'مانشستر يونايتد',
        'Arsenal' => 'أرسنال', 'Liverpool' => 'ليفربول', 'Chelsea' => 'تشيلسي',
        'Tottenham' => 'توتنهام', 'Tottenham Hotspur' => 'توتنهام',

        // Spain
        'Real Madrid' => 'ريال مدريد', 'Barcelona' => 'برشلونة', 'FC Barcelona' => 'برشلونة',
        'Atletico Madrid' => 'أتلتيكو مدريد',

        // Germany
        'Bayern Munich' => 'بايرن ميونخ', 'Bayern München' => 'بايرن ميونخ',

        // France
        'PSG' => 'باريس سان جيرمان', 'Paris SG' => 'باريس سان جيرمان',
        'Paris Saint-Germain' => 'باريس سان جيرمان',

        // Italy
        'Inter' => 'إنتر', 'Inter Milan' => 'إنتر ميلان',
        'AC Milan' => 'ميلان', 'Milan' => 'ميلان',
        'Juventus' => 'يوفنتوس', 'Napoli' => 'نابولي', 'Atalanta' => 'أتالانتا',
    ];

    private function translate($name)
    {
        return $this->arabicDictionary[$name] ?? $name;
    }

    public function index(Request $request)
    {
        $selectedDate = $request->date ?: date('Y-m-d');
        $apiDate = date('Ymd', strtotime($selectedDate));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://{$this->apiHost}/matches/v2/list-by-date?Category=soccer&Date=" . $apiDate,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: {$this->apiHost}",
                "x-rapidapi-key: {$this->apiKey}",
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => $err], 502);
        }

        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $remaining = 'غير معروف';
        if (preg_match('/x-ratelimit-requests-remaining:\s*(\d+)/i', $header, $m)) {
            $remaining = $m[1];
        }

        $data = json_decode($body, true);
        $stages = $data['Stages'] ?? [];

        $matches = [];
        $leagues = [];

        foreach ($stages as $stage) {
            $leagueName = $stage['Snm'] ?? '';
            $countryName = $stage['Cnm'] ?? '';
            $compId = $stage['CompId'] ?? null;
            $compN = $stage['CompN'] ?? $leagueName;
            $badgeUrl = $stage['badgeUrl'] ?? null;
            $leagueLower = mb_strtolower($leagueName);
            $compLower = mb_strtolower($compN);

            // استبعاد النسائية والشباب والرديف
            $excluded = false;
            $excludePatterns = [
                'women', 'womens', 'femminile', 'femenino', 'feminina', 'feminino',
                'u19', 'u20', 'u21', 'u23', 'u18', 'u17', 'primavera', 'youth', 'reserve', 'junior',
                ' ii ', 'championship', 'league 1', 'league 2', 'serie b', 'segunda', 'laliga2', 'laliga 2',
            ];
            foreach ($excludePatterns as $p) {
                if (str_contains($leagueLower, $p) || str_contains($compLower, $p)) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) continue;

            // البطولات الكبرى (بدون فلترة فرق)
            $isMajor = false;
            if ($countryName === '') {
                foreach (['champions league', 'world cup'] as $m) {
                    if (str_contains($leagueLower, $m) || str_contains($compLower, $m)) {
                        $isMajor = true;
                        break;
                    }
                }
            }

            // فلترة الدولة للبطولات غير الكبرى
            if (!$isMajor) {
                $cl = strtolower(trim($countryName));
                $allowedCountries = ['england', 'united kingdom', 'uk', 'great britain', 'gbr', 'gb', 'britain', 'british', 'spain', 'italy', 'germany', 'france'];
                $countryOk = false;
                if ($cl === '') {
                    $known = ['premier league', 'fa cup', 'efl cup', 'laliga', 'copa del rey', 'serie a', 'coppa italia', 'bundesliga', 'dfb pokal', 'ligue 1', 'coupe de france'];
                    foreach ($known as $k) {
                        if (str_contains($leagueLower, $k) || str_contains($compLower, $k)) { $countryOk = true; break; }
                    }
                } else {
                    foreach ($allowedCountries as $ac) {
                        if (str_contains($cl, $ac)) { $countryOk = true; break; }
                    }
                }
                if (!$countryOk) continue;
            }

            // معالجة الأحداث
            $leagueKey = $compId ?: $leagueName;
            $stageMatches = [];

            foreach (($stage['Events'] ?? []) as $event) {
                $homeTeamRaw = trim($event['T1'][0]['Nm'] ?? '');
                $awayTeamRaw = trim($event['T2'][0]['Nm'] ?? '');

                // فلترة الفرق للبطولات غير الكبرى
                if (!$isMajor) {
                    $homeLower = mb_strtolower($homeTeamRaw);
                    $awayLower = mb_strtolower($awayTeamRaw);
                    $teamOk = false;
                    foreach ($this->allowedTeams as $at) {
                        $atl = mb_strtolower(trim($at));
                        if ($homeLower === $atl || $awayLower === $atl) { $teamOk = true; break; }
                    }
                    if (!$teamOk) continue;
                }

                $homeTeam = $this->translate($homeTeamRaw);
                $awayTeam = $this->translate($awayTeamRaw);
                $homeImg = isset($event['T1'][0]['Img']) ? "https://livescore6.p.rapidapi.com/static/images/" . $event['T1'][0]['Img'] : null;
                $awayImg = isset($event['T2'][0]['Img']) ? "https://livescore6.p.rapidapi.com/static/images/" . $event['T2'][0]['Img'] : null;
                $status = $event['Eps'] ?? 'NS';
                $homeScore = $event['Tr1'] ?? null;
                $awayScore = $event['Tr2'] ?? null;

                // الوقت من Esd مباشرة (الـ API يعطيه بالتوقيت المحلي)
                $esd = isset($event['Esd']) ? (string)$event['Esd'] : '';
                $matchTime = null;
                $matchDate = null;

                if (strlen($esd) >= 12) {
                    $year = substr($esd, 0, 4);
                    $month = substr($esd, 4, 2);
                    $day = substr($esd, 6, 2);
                    $hour = substr($esd, 8, 2);
                    $minute = substr($esd, 10, 2);
                    $matchTime = $hour . ':' . $minute;
                    $matchDate = "$year-$month-$day $hour:$minute:00";
                    $esdDate = "$year-$month-$day";
                    if ($esdDate !== $selectedDate) continue;
                }

                // تحديد حالة المباراة
                $isUpcoming = in_array($status, ['NS', 'SCHEDULED', 'TIMED', 'POSTPONED', 'INT', 'SUSP']);
                $isFinished = in_array($status, ['FT', 'AET', 'PEN', 'FINISHED', 'ABAN', 'AWARDED', 'WO', 'CANCELED']);
                $isLive = !$isUpcoming && !$isFinished;

                $statusText = match ($status) {
                    'NS', 'SCHEDULED', 'TIMED' => 'مجدول',
                    '1H' => 'مباشر - شوط أول',
                    '2H' => 'مباشر - شوط ثاني',
                    'HT' => 'بين الشوطين',
                    'ET' => 'مباشر - وقت إضافي',
                    'FT' => 'انتهت',
                    'AET' => 'انتهت (وقت إضافي)',
                    'PEN' => 'انتهت (ركلات)',
                    'FINISHED' => 'انتهت',
                    'ABAN', 'AWARDED', 'WO', 'CANCELED' => 'ملغية',
                    'POSTPONED' => 'مؤجلة',
                    'INT', 'SUSP' => 'متوقفة',
                    default => $status,
                };

                $stageMatches[] = [
                    'id' => $event['Eid'] ?? null,
                    'league_id' => $leagueKey,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'home_img' => $homeImg,
                    'away_img' => $awayImg,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => $status,
                    'status_text' => $statusText,
                    'is_finished' => $isFinished,
                    'is_live' => $isLive,
                    'match_time' => $matchTime,
                    'match_date' => $matchDate,
                    'round' => $event['ErnInf'] ?? null,
                ];
            }

            if (empty($stageMatches)) continue;

            $translatedName = $this->translate($leagueName);
            $translatedCountry = $this->translate($countryName);
            $leagues[$leagueKey] = [
                'id' => $leagueKey,
                'name' => $translatedName,
                'country' => $translatedCountry,
                'competition' => $compN,
                'badge_url' => $badgeUrl ? "https://livescore6.p.rapidapi.com/static/images/badges/{$badgeUrl}" : null,
                'matches' => $stageMatches,
            ];
            $matches = array_merge($matches, $stageMatches);
        }

        return response()->json([
            'leagues' => array_values($leagues),
            'matches' => $matches,
            'date' => $selectedDate,
            'api_date' => $apiDate,
            'remaining_requests' => $remaining,
        ]);
    }
}

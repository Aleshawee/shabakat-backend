<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MatchApiController extends Controller
{
    private $apiKey = '5ee67b3b7fc649d380689eda809bcf02';

    private $competitions = [
        'PL'  => ['name' => 'الدوري الإنجليزي الممتاز',   'country' => 'إنجلترا', 'filter' => true],
        'PD'  => ['name' => 'الدوري الإسباني',            'country' => 'إسبانيا', 'filter' => true],
        'SA'  => ['name' => 'الدوري الإيطالي',            'country' => 'إيطاليا', 'filter' => true],
        'BL1' => ['name' => 'الدوري الألماني',            'country' => 'ألمانيا', 'filter' => true],
        'FL1' => ['name' => 'الدوري الفرنسي',            'country' => 'فرنسا',   'filter' => true],
        'CL'  => ['name' => 'دوري أبطال أوروبا',         'country' => '',         'filter' => false],
        'WC'  => ['name' => 'كأس العالم',                 'country' => '',         'filter' => false],
    ];

    private $favoriteTeams = [
        'Real Madrid CF', 'FC Barcelona', 'Atletico Madrid',
        'Manchester City FC', 'Manchester United FC', 'Chelsea FC', 'Arsenal FC', 'Liverpool FC',
        'FC Bayern München',
        'Paris Saint-Germain FC',
        'Inter', 'AC Milan', 'Juventus FC', 'SSC Napoli', 'Atalanta BC',
    ];

    private $teamNames = [
        // أندية أوروبية
        'Real Madrid CF' => 'ريال مدريد', 'FC Barcelona' => 'برشلونة',
        'Atletico Madrid' => 'أتلتيكو مدريد',
        'Manchester City FC' => 'مانشستر سيتي',
        'Manchester United FC' => 'مانشستر يونايتد',
        'Chelsea FC' => 'تشيلسي', 'Arsenal FC' => 'أرسنال', 'Liverpool FC' => 'ليفربول',
        'Tottenham Hotspur FC' => 'توتنهام', 'Aston Villa FC' => 'أستون فيلا', 'Newcastle United FC' => 'نيوكاسل',
        'FC Bayern München' => 'بايرن ميونخ',
        'Borussia Dortmund' => 'بوروسيا دورتموند', 'Bayer 04 Leverkusen' => 'باير ليفركوزن',
        'RB Leipzig' => 'لايبزيغ', 'Eintracht Frankfurt' => 'آينتراخت فرانكفورت',
        'Paris Saint-Germain FC' => 'باريس سان جيرمان',
        'Olympique Marseille' => 'مارسيليا', 'AS Monaco FC' => 'موناكو', 'Olympique Lyonnais' => 'ليون',
        'Inter' => 'إنتر ميلان', 'AC Milan' => 'ميلان',
        'Juventus FC' => 'يوفنتوس', 'SSC Napoli' => 'نابولي', 'Atalanta BC' => 'أتالانتا',
        'AS Roma' => 'روما', 'SS Lazio' => 'لاتسيو',
        'FC Porto' => 'بورتو', 'SL Benfica' => 'بنفيكا', 'Sporting CP' => 'سبورتينغ لشبونة',
        'AFC Ajax' => 'أياكس', 'PSV Eindhoven' => 'آيندهوفن',
        // منتخبات كأس العالم
        'Argentina' => 'الأرجنتين', 'Brazil' => 'البرازيل', 'Uruguay' => 'الأوروغواي',
        'Colombia' => 'كولومبيا', 'Ecuador' => 'الإكوادور', 'Peru' => 'بيرو',
        'Chile' => 'تشيلي', 'Paraguay' => 'باراغواي', 'Venezuela' => 'فنزويلا',
        'Germany' => 'ألمانيا', 'France' => 'فرنسا', 'Spain' => 'إسبانيا',
        'England' => 'إنجلترا', 'Italy' => 'إيطاليا', 'Netherlands' => 'هولندا',
        'Portugal' => 'البرتغال', 'Belgium' => 'بلجيكا', 'Croatia' => 'كرواتيا',
        'Denmark' => 'الدنمارك', 'Switzerland' => 'سويسرا', 'Serbia' => 'صربيا',
        'Sweden' => 'السويد', 'Norway' => 'النرويج', 'Poland' => 'بولندا',
        'Ukraine' => 'أوكرانيا', 'Austria' => 'النمسا', 'Czech Republic' => 'التشيك',
        'Wales' => 'ويلز', 'Scotland' => 'اسكتلندا', 'Hungary' => 'المجر',
        'Türkiye' => 'تركيا', 'Turkey' => 'تركيا', 'Greece' => 'اليونان',
        'Russia' => 'روسيا', 'Romania' => 'رومانيا', 'Slovakia' => 'سلوفاكيا',
        'Slovenia' => 'سلوفينيا', 'Montenegro' => 'الجبل الأسود',
        'Bosnia-Herzegovina' => 'البوسنة والهرسك', 'Iceland' => 'آيسلندا',
        'USA' => 'الولايات المتحدة', 'Mexico' => 'المكسيك', 'Canada' => 'كندا',
        'Costa Rica' => 'كوستاريكا', 'Panama' => 'بنما', 'Honduras' => 'هندوراس',
        'Jamaica' => 'جامايكا', 'Trinidad and Tobago' => 'ترينيداد وتوباغو',
        'Nigeria' => 'نيجيريا', 'Ghana' => 'غانا', 'Senegal' => 'السنغال',
        'Cameroon' => 'الكاميرون', 'Ivory Coast' => 'ساحل العاج',
        'Côte d\'Ivoire' => 'ساحل العاج', 'Algeria' => 'الجزائر',
        'Morocco' => 'المغرب', 'Tunisia' => 'تونس', 'Egypt' => 'مصر',
        'South Africa' => 'جنوب أفريقيا', 'Mali' => 'مالي',
        'Burkina Faso' => 'بوركينا فاسو', 'Congo DR' => 'الكونغو الديمقراطية',
        'Japan' => 'اليابان', 'South Korea' => 'كوريا الجنوبية',
        'Korea Republic' => 'كوريا الجنوبية', 'Australia' => 'أستراليا',
        'Saudi Arabia' => 'السعودية', 'Iran' => 'إيران',
        'Qatar' => 'قطر', 'United Arab Emirates' => 'الإمارات',
        'Iraq' => 'العراق', 'Syria' => 'سوريا', 'Jordan' => 'الأردن',
        'Oman' => 'عُمان', 'Bahrain' => 'البحرين', 'Kuwait' => 'الكويت',
        'Uzbekistan' => 'أوزبكستان', 'China' => 'الصين',
        'New Zealand' => 'نيوزيلندا',
    ];

    public function index(Request $request)
    {
        $date = $request->date ?: date('Y-m-d');
        $allLeagues = [];
        $allMatches = [];

        foreach ($this->competitions as $code => $settings) {
            $leagueName = $settings['name'];
            $countryName = $settings['country'];
            $shouldFilter = $settings['filter'];

            $url = "https://api.football-data.org/v4/competitions/$code/matches?dateFrom=$date&dateTo=$date";
            $response = $this->fetch($url);
            if (!$response) continue;

            $data = json_decode($response, true);
            if (empty($data['matches'])) continue;

            $stageMatches = [];
            foreach ($data['matches'] as $match) {
                $homeTeam = $match['homeTeam']['name'] ?? '';
                $awayTeam = $match['awayTeam']['name'] ?? '';

                if ($shouldFilter) {
                    if (!in_array($homeTeam, $this->favoriteTeams) && !in_array($awayTeam, $this->favoriteTeams)) continue;
                }

                $homeTeamAr = $this->teamNames[$homeTeam] ?? $homeTeam;
                $awayTeamAr = $this->teamNames[$awayTeam] ?? $awayTeam;

                $status = $match['status'] ?? 'SCHEDULED';
                $isFinished = $status === 'FINISHED';
                $isLive = in_array($status, ['LIVE', 'IN_PLAY', 'PAUSED']);
                $isScheduled = in_array($status, ['SCHEDULED', 'TIMED', 'POSTPONED']);

                $homeScore = $match['score']['fullTime']['home'] ?? ($match['score']['halfTime']['home'] ?? null);
                $awayScore = $match['score']['fullTime']['away'] ?? ($match['score']['halfTime']['away'] ?? null);

                $utcDateStr = $match['utcDate'] ?? '';
                $matchTime = null;
                $matchDate = null;

                if ($utcDateStr) {
                    try {
                        $dt = new \DateTime($utcDateStr);
                        $dt->setTimezone(new \DateTimeZone('Asia/Riyadh'));
                        $matchTime = $dt->format('H:i');
                        $matchDate = $dt->format('Y-m-d\TH:i:s');
                    } catch (\Exception $e) {}
                }

                $statusText = match ($status) {
                    'SCHEDULED', 'TIMED' => 'مجدول',
                    'LIVE', 'IN_PLAY' => 'مباشر',
                    'PAUSED' => 'متوقف',
                    'FINISHED' => 'انتهت',
                    'POSTPONED' => 'مؤجلة',
                    'CANCELED' => 'ملغية',
                    default => $status,
                };

                $stageMatches[] = [
                    'id' => $match['id'] ?? null,
                    'league_id' => $code,
                    'home_team' => $homeTeamAr,
                    'away_team' => $awayTeamAr,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => $status,
                    'status_text' => $statusText,
                    'is_finished' => $isFinished,
                    'is_live' => $isLive,
                    'match_time' => $matchTime,
                    'match_date' => $matchDate,
                    'round' => $match['matchday'] ?? null,
                ];
            }

            if (empty($stageMatches)) continue;

            $allLeagues[] = [
                'id' => $code,
                'name' => $leagueName,
                'country' => $countryName,
                'badge_url' => null,
                'matches' => $stageMatches,
            ];
            $allMatches = array_merge($allMatches, $stageMatches);
        }

        return response()->json([
            'leagues' => $allLeagues,
            'matches' => $allMatches,
            'date' => $date,
            'remaining_requests' => $this->remaining,
        ]);
    }

    private $remaining = 'غير معروف';

    private function fetch($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => ["X-Auth-Token: {$this->apiKey}"],
        ]);
        $res = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($http === 200) {
            $header = substr($res, 0, $headerSize);
            if (preg_match('/X-Requests-Available-Minute:\s*(\d+)/i', $header, $m)) {
                $this->remaining = (int)$m[1];
            }
            return substr($res, $headerSize);
        }
        return null;
    }
}

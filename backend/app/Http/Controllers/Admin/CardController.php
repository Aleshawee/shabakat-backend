<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetworkCard;
use App\Models\RewardCard;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CardController extends Controller
{
    // ========== Helpers ==========

    private function applyFilters($query, Request $request, array $options = [])
    {
        $statusField = $options['status_field'] ?? 'status';
        $foreignField = $options['foreign_field'] ?? null;

        if ($request->search) {
            $query->where('code', 'like', "%{$request->search}%");
        }
        if ($request->status) {
            $query->where($statusField, $request->status);
        }
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($foreignField && $request->filled($foreignField)) {
            $query->where($foreignField, $request->{$foreignField});
        }
        // trashed: with / only
        if ($request->trashed === 'with') {
            $query->withTrashed();
        } elseif ($request->trashed === 'only') {
            $query->onlyTrashed();
        }
        return $query;
    }

    private function extractCodes(Request $request): array
    {
        $codes = [];
        if ($request->filled('codes')) {
            $parts = array_filter(explode("\n", str_replace("\r", "", $request->codes)));
            foreach ($parts as $c) {
                $c = trim(explode(',', $c)[0]);
                if (!empty($c)) $codes[] = $c;
            }
        }
        if ($request->hasFile('file')) {
            $content = file_get_contents($request->file('file')->getRealPath());
            $lines = array_filter(explode("\n", str_replace("\r", "", $content)));
            foreach ($lines as $line) {
                $c = trim(explode(',', $line)[0]);
                if (!empty($c)) $codes[] = $c;
            }
        }
        return array_unique($codes);
    }

    private function makeCode($code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9\-_]/', '', $code));
    }

    private function log($action, $details, $admin)
    {
        ActivityLog::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'details' => $details,
        ]);
    }

    // ========== Stats ==========

    public function stats(Request $request)
    {
        $rewardBase = RewardCard::query();
        $networkBase = NetworkCard::query();

        return response()->json([
            'reward_cards' => [
                'total' => (clone $rewardBase)->count(),
                'available' => (clone $rewardBase)->where('status', 'available')->count(),
                'redeemed' => (clone $rewardBase)->where('status', 'redeemed')->count(),
                'expired' => (clone $rewardBase)->where('status', 'expired')->count(),
                'trashed' => (clone $rewardBase)->onlyTrashed()->count(),
            ],
            'network_cards' => [
                'total' => (clone $networkBase)->count(),
                'active' => (clone $networkBase)->where('status', 'active')->count(),
                'used' => (clone $networkBase)->where('status', 'used')->count(),
                'expired' => (clone $networkBase)->where('status', 'expired')->count(),
                'trashed' => (clone $networkBase)->onlyTrashed()->count(),
            ],
        ]);
    }

    public function rewardCardStats(Request $request)
    {
        $query = RewardCard::query();
        return response()->json([
            'total' => (clone $query)->count(),
            'available' => (clone $query)->where('status', 'available')->count(),
            'redeemed' => (clone $query)->where('status', 'redeemed')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'trashed' => (clone $query)->onlyTrashed()->count(),
        ]);
    }

    public function networkCardStats(Request $request)
    {
        $query = NetworkCard::query();
        return response()->json([
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'used' => (clone $query)->where('status', 'used')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'trashed' => (clone $query)->onlyTrashed()->count(),
        ]);
    }

    // ========== Reward Cards ==========

    public function rewardCards(Request $request)
    {
        $query = RewardCard::with('reward');
        $query = $this->applyFilters($query, $request, [
            'status_field' => 'status',
            'foreign_field' => 'reward_id',
        ]);
        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function importRewardCards(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,id',
            'codes' => 'nullable|string',
            'file' => 'nullable|file|mimes:csv,txt|max:10240',
        ]);
        $codes = $this->extractCodes($request);
        if (empty($codes)) return response()->json(['message' => 'لا توجد أكواد صالحة'], 422);
        $inserted = 0;
        foreach ($codes as $code) {
            $code = $this->makeCode($code);
            if (empty($code)) continue;
            try {
                RewardCard::create(['reward_id' => $request->reward_id, 'code' => $code]);
                $inserted++;
            } catch (\Exception $e) {}
        }
        $this->log('import_reward_cards', "استيراد {$inserted} كرت مكافأة", $admin);
        return response()->json(['message' => "تم استيراد {$inserted} كرت بنجاح"]);
    }

    public function deleteRewardCard($id)
    {
        $card = RewardCard::findOrFail($id);
        $card->delete();
        return response()->json(['message' => 'تم نقل الكرت إلى سلة المحذوفات']);
    }

    public function restoreRewardCard($id)
    {
        $card = RewardCard::withTrashed()->findOrFail($id);
        $card->restore();
        return response()->json(['message' => 'تم استعادة الكرت']);
    }

    public function forceDeleteRewardCard($id)
    {
        $card = RewardCard::withTrashed()->findOrFail($id);
        $card->forceDelete();
        return response()->json(['message' => 'تم الحذف النهائي']);
    }

    // ========== Network Cards ==========

    public function networkCards(Request $request)
    {
        $query = NetworkCard::with('category');
        $query = $this->applyFilters($query, $request, [
            'status_field' => 'status',
            'foreign_field' => 'category_id',
        ]);
        $perPage = min((int) $request->per_page, 100) ?: 50;
        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function importNetworkCards(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'codes' => 'nullable|string',
            'file' => 'nullable|file|mimes:csv,txt|max:10240',
        ]);
        $codes = $this->extractCodes($request);
        if (empty($codes)) return response()->json(['message' => 'لا توجد أكواد صالحة'], 422);
        $inserted = 0;
        foreach ($codes as $code) {
            $code = $this->makeCode($code);
            if (empty($code)) continue;
            try {
                NetworkCard::create(['category_id' => $request->category_id, 'code' => $code]);
                $inserted++;
            } catch (\Exception $e) {}
        }
        return response()->json(['message' => "تم استيراد {$inserted} كرت بنجاح"]);
    }

    public function deleteNetworkCard($id)
    {
        $card = NetworkCard::findOrFail($id);
        $card->delete();
        return response()->json(['message' => 'تم نقل الكرت إلى سلة المحذوفات']);
    }

    public function restoreNetworkCard($id)
    {
        $card = NetworkCard::withTrashed()->findOrFail($id);
        $card->restore();
        return response()->json(['message' => 'تم استعادة الكرت']);
    }

    public function forceDeleteNetworkCard($id)
    {
        $card = NetworkCard::withTrashed()->findOrFail($id);
        $card->forceDelete();
        return response()->json(['message' => 'تم الحذف النهائي']);
    }

    // ========== Bulk Actions ==========

    private function bulkAction($modelClass, Request $request, string $action)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'يرجى تحديد كروت أولاً'], 422);
        }
        $query = $modelClass::query();
        if (in_array($action, ['restore', 'forceDelete'])) {
            $query = $modelClass::withTrashed();
        }
        if ($action === 'forceDelete') {
            $query->whereIn('id', $ids)->forceDelete();
        } elseif ($action === 'restore') {
            $query->whereIn('id', $ids)->restore();
        } else {
            $query->whereIn('id', $ids)->delete();
        }
        $count = count($ids);
        return response()->json(['message' => "تم تطبيق الإجراء على {$count} كرت"]);
    }

    public function bulkDeleteRewardCards(Request $request) { return $this->bulkAction(RewardCard::class, $request, 'delete'); }
    public function bulkRestoreRewardCards(Request $request) { return $this->bulkAction(RewardCard::class, $request, 'restore'); }
    public function bulkForceDeleteRewardCards(Request $request) { return $this->bulkAction(RewardCard::class, $request, 'forceDelete'); }
    public function bulkDeleteNetworkCards(Request $request) { return $this->bulkAction(NetworkCard::class, $request, 'delete'); }
    public function bulkRestoreNetworkCards(Request $request) { return $this->bulkAction(NetworkCard::class, $request, 'restore'); }
    public function bulkForceDeleteNetworkCards(Request $request) { return $this->bulkAction(NetworkCard::class, $request, 'forceDelete'); }
}
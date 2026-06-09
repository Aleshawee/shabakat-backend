<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'password', 'device_id',
        'points_balance', 'wallet_balance', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
        ];
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function cardRedemptions()
    {
        return $this->hasMany(CardRedemption::class);
    }

    public function deviceFingerprints()
    {
        return $this->hasMany(DeviceFingerprint::class);
    }

    public function networkCards()
    {
        return $this->hasMany(NetworkCard::class, 'used_by_user_id');
    }

    public function rewardCards()
    {
        return $this->hasMany(RewardCard::class, 'redeemed_by_user_id');
    }

    public function addPointsWithRepayment(int $points, string $referenceType, ?int $referenceId = null, string $note = ''): int
    {
        $activeBorrow = PointBorrow::where('user_id', $this->id)
            ->where('status', 'active')
            ->first();

        if ($activeBorrow && $this->points_balance < 0) {
            $remainingDebt = $activeBorrow->total_debt - $activeBorrow->repaid_amount;
            $repayAmount = min($points, $remainingDebt);

            $activeBorrow->increment('repaid_amount', $repayAmount);
            if ($activeBorrow->repaid_amount >= $activeBorrow->total_debt) {
                $activeBorrow->update(['status' => 'repaid', 'repaid_at' => now()]);
            }

            // تسديد السلفة يزيد الرصيد فعلياً
            $this->increment('points_balance', $repayAmount);

            PointTransaction::create([
                'user_id' => $this->id,
                'type' => 'borrow_repayment',
                'amount' => $repayAmount,
                'balance_after' => $this->points_balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => 'تسديد سلفة: ' . $note,
            ]);

            $points -= $repayAmount;
        }

        if ($points > 0) {
            $this->increment('points_balance', $points);
        }

        return $this->fresh()->points_balance;
    }
}

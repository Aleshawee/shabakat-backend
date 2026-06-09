<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferSetting extends Model
{
    protected $fillable = ['is_enabled', 'min_transfer_amount', 'max_transfer_amount', 'transfer_fee_percent', 'min_balance_required', 'require_phone_verification'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'transfer_fee_percent' => 'decimal:1',
        ];
    }

}

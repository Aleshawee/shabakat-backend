<?php

namespace App\Console\Commands;

use App\Models\SportEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseSportEvents extends Command
{
    protected $signature = 'sport-events:auto-close';
    protected $description = 'إغلاق الأحداث الرياضية تلقائياً عند انتهاء الموعد النهائي';

    public function handle()
    {
        // نمرر الوقت كـ string بتوقيت الرياض لأن العمود prediction_deadline
        // مخزن رقمياً بتوقيت الرياض ولكن Carbon/Casts يعامله كـ UTC
        $nowStr = Carbon::now('Asia/Riyadh')->format('Y-m-d H:i:s');
        $closed = SportEvent::where('status', 'open')
            ->where('prediction_deadline', '<=', $nowStr)
            ->update(['status' => 'closed']);

        $this->info("تم إغلاق {$closed} حدث/أحداث تلقائياً");
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\LuckyBoxController;
use App\Http\Controllers\Admin\LuckyWheelController;
use App\Http\Controllers\Admin\SportEventController;
use App\Http\Controllers\Admin\PredictionSettingController;
use App\Http\Controllers\Admin\MatchApiController;
use App\Http\Controllers\Admin\QuizContestController;
use App\Http\Controllers\Admin\AbsherSettingController;
use App\Http\Controllers\Admin\TransferSettingController;
use App\Http\Controllers\Admin\RestrictionController;
use App\Http\Controllers\Admin\NetworkAdminController;

/*
  API الإدارة — للوحة تحكم Vue.js
  كل المسارات تحت prefix: /api/admin
*/

Route::prefix('v1')->group(function () {
    Route::post('admin/login', [AdminAuthController::class, 'login']);
    Route::post('auth/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'tenancy.by_admin'])->group(function () {
        Route::get('admin/profile', [AdminAuthController::class, 'profile']);
        Route::post('admin/logout', [AdminAuthController::class, 'logout']);

        // لوحة التحكم — إحصائيات موحدة
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

        // إدارة الفئات
        Route::get('categories/stats', [CategoryController::class, 'stats']);
        Route::apiResource('categories', CategoryController::class);

        // إدارة المكافآت
        Route::apiResource('rewards', RewardController::class);

        // إدارة الكروت
        Route::get('cards/stats', [CardController::class, 'stats']);
        // كروت المكافأة — الثابتة قبل المتغيرة
        Route::get('reward-cards/stats', [CardController::class, 'rewardCardStats']);
        Route::get('reward-cards', [CardController::class, 'rewardCards']);
        Route::post('reward-cards/import', [CardController::class, 'importRewardCards']);
        Route::post('reward-cards/bulk-delete', [CardController::class, 'bulkDeleteRewardCards']);
        Route::post('reward-cards/bulk-restore', [CardController::class, 'bulkRestoreRewardCards']);
        Route::post('reward-cards/bulk-force-delete', [CardController::class, 'bulkForceDeleteRewardCards']);
        Route::delete('reward-cards/{id}', [CardController::class, 'deleteRewardCard']);
        Route::post('reward-cards/{id}/restore', [CardController::class, 'restoreRewardCard']);
        Route::delete('reward-cards/{id}/force', [CardController::class, 'forceDeleteRewardCard']);
        // كروت الشبكة — الثابتة قبل المتغيرة
        Route::get('network-cards/stats', [CardController::class, 'networkCardStats']);
        Route::get('network-cards', [CardController::class, 'networkCards']);
        Route::post('network-cards/import', [CardController::class, 'importNetworkCards']);
        Route::post('network-cards/bulk-delete', [CardController::class, 'bulkDeleteNetworkCards']);
        Route::post('network-cards/bulk-restore', [CardController::class, 'bulkRestoreNetworkCards']);
        Route::post('network-cards/bulk-force-delete', [CardController::class, 'bulkForceDeleteNetworkCards']);
        Route::delete('network-cards/{id}', [CardController::class, 'deleteNetworkCard']);
        Route::post('network-cards/{id}/restore', [CardController::class, 'restoreNetworkCard']);
        Route::delete('network-cards/{id}/force', [CardController::class, 'forceDeleteNetworkCard']);

        // إدارة المستخدمين
        Route::get('users/stats', [UserController::class, 'stats']);
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::get('users/{id}/point-transactions', [UserController::class, 'pointTransactions']);
        Route::get('users/{id}/network-cards', [UserController::class, 'networkCards']);
        Route::get('users/{id}/reward-cards', [UserController::class, 'rewardCards']);
        Route::post('users/{id}/add-points', [UserController::class, 'addPoints']);
        Route::post('users/add-points-bulk', [UserController::class, 'addPointsBulk']);

        // إدارة الإشعارات
        Route::get('notifications/stats', [NotificationController::class, 'stats']);
        Route::apiResource('notifications', NotificationController::class);

        // إدارة البانرات
        Route::get('banners/stats', [BannerController::class, 'stats']);
        Route::apiResource('banners', BannerController::class);

        // إعدادات SMS
        Route::get('settings/sms', [SettingController::class, 'sms']);
        Route::put('settings/sms', [SettingController::class, 'updateSms']);

        // إرسال الرسائل النصية
        Route::get('sms/stats', [SmsController::class, 'stats']);
        Route::get('sms/history', [SmsController::class, 'history']);
        Route::post('sms/count-target', [SmsController::class, 'countTargetUsers']);
        Route::post('sms/send', [SmsController::class, 'send']);
        Route::post('sms/test-textbee', [SmsController::class, 'testTextbee']);
        Route::post('sms/test-provider', [SmsController::class, 'testProvider']);
        Route::post('sms/fetch-received', [SmsController::class, 'fetchReceived']);

        // الترفيه — صندوق الحظ
        Route::get('lucky-boxes', [LuckyBoxController::class, 'index']);
        Route::post('lucky-boxes/save', [LuckyBoxController::class, 'save']);
        // الترفيه — عجلة الحظ
        Route::get('lucky-wheels', [LuckyWheelController::class, 'index']);
        Route::post('lucky-wheels/save', [LuckyWheelController::class, 'save']);
        Route::post('lucky-wheels/{id}/reset-free-spins', [LuckyWheelController::class, 'resetFreeSpins']);
        Route::post('lucky-wheels/{id}/reset-free-spins/user', [LuckyWheelController::class, 'resetFreeSpinsForUser']);
        // الترفيه — توقعات الرياضة
Route::get('sport-events/stats', [SportEventController::class, 'stats']);
Route::apiResource('sport-events', SportEventController::class);
Route::post('sport-events/{sport_event}/distribute', [SportEventController::class, 'distribute']);
Route::get('predictions/settings', [PredictionSettingController::class, 'show']);
Route::put('predictions/settings', [PredictionSettingController::class, 'update']);
Route::get('predictions/matches', [MatchApiController::class, 'index']);
        // الترفيه — المسابقات + الأسئلة
        Route::apiResource('quiz-contests', QuizContestController::class);
        Route::get('quiz-contests/{contest}/questions', [QuizContestController::class, 'questions']);
        Route::post('quiz-contests/{contest}/questions', [QuizContestController::class, 'storeQuestion']);
        Route::put('quiz-contests/{contest}/questions/{question}', [QuizContestController::class, 'updateQuestion']);
        Route::delete('quiz-contests/{contest}/questions/{question}', [QuizContestController::class, 'destroyQuestion']);

        // أبشر — إعدادات السلفة
        Route::get('absher/settings', [AbsherSettingController::class, 'show']);
        Route::put('absher/settings', [AbsherSettingController::class, 'update']);

        // تحويل النقاط — الإعدادات
        Route::get('transfer/settings', [TransferSettingController::class, 'show']);
        Route::put('transfer/settings', [TransferSettingController::class, 'update']);
        Route::get('transfer/lookup-user', [TransferSettingController::class, 'lookupUser']);

        // التحليلات والإحصائيات
        Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index']);

        // سجل عمليات الاستبدال
        Route::get('redemptions', [\App\Http\Controllers\Admin\RedemptionController::class, 'index']);

        // قيود المستخدمين
        Route::get('restrictions', [RestrictionController::class, 'show']);
        Route::put('restrictions', [RestrictionController::class, 'update']);
        Route::post('reset-points/by-phone', [RestrictionController::class, 'resetByPhone']);
        Route::post('reset-points/user/{id}', [RestrictionController::class, 'resetUser']);
        Route::post('reset-points/all', [RestrictionController::class, 'resetAll']);

        // مدراء الشبكة — إدارة المدراء الفرعيين
        Route::get('network-admins', [NetworkAdminController::class, 'index']);
        Route::post('network-admins', [NetworkAdminController::class, 'store']);
        Route::put('network-admins/{id}', [NetworkAdminController::class, 'update']);
        Route::delete('network-admins/{id}', [NetworkAdminController::class, 'destroy']);

        // المالك — إدارة الشبكات والمدراء
        Route::middleware('owner')->prefix('owner')->group(function () {
            Route::get('stats', [\App\Http\Controllers\Admin\SuperAdminController::class, 'stats']);
            Route::get('networks', [\App\Http\Controllers\Admin\SuperAdminController::class, 'networks']);
            Route::post('networks', [\App\Http\Controllers\Admin\SuperAdminController::class, 'storeNetwork']);
            Route::put('networks/{id}', [\App\Http\Controllers\Admin\SuperAdminController::class, 'updateNetwork']);
            Route::delete('networks/{id}', [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteNetwork']);
            Route::get('networks/{networkId}/admins', [\App\Http\Controllers\Admin\SuperAdminController::class, 'listAdmins']);
            Route::post('networks/{networkId}/admins', [\App\Http\Controllers\Admin\SuperAdminController::class, 'storeAdmin']);
            Route::put('networks/{networkId}/admins/{id}', [\App\Http\Controllers\Admin\SuperAdminController::class, 'updateAdmin']);
            Route::delete('networks/{networkId}/admins/{id}', [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteAdmin']);
        });
    });
});

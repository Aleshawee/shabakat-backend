<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== Admin & Access Control =====
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin'])->default('admin');
            $table->json('permissions')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        // ===== Users =====
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('device_id')->nullable();
            $table->integer('points_balance')->default(0);
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active');
            $table->timestamp('last_active_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ===== Categories =====
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ===== Rewards =====
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points_cost')->default(0);
            $table->decimal('card_value', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ===== Reward Cards =====
        Schema::create('reward_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reward_id');
            $table->unsignedBigInteger('redeemed_by_user_id')->nullable();
            $table->string('code')->unique();
            $table->enum('status', ['available', 'redeemed', 'expired'])->default('available');
            $table->timestamp('redeemed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // ===== Network Cards =====
        Schema::create('network_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('code')->unique();
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by_user_id')->nullable();
            $table->string('batch_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // ===== Card Redemptions =====
        Schema::create('card_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_card_id')->nullable();
            $table->unsignedBigInteger('reward_id');
            $table->integer('points_spent');
            $table->timestamps();
        });

        // ===== Point Transactions =====
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->integer('amount');
            $table->integer('balance_after')->nullable();
            $table->nullableMorphs('reference');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ===== Point Transfers =====
        Schema::create('point_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->integer('amount');
            $table->integer('fee')->default(0);
            $table->integer('gross_amount');
            $table->enum('status', ['completed', 'failed'])->default('completed');
            $table->timestamps();
        });

        // ===== Point Borrows =====
        Schema::create('point_borrows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('amount');
            $table->integer('fee')->default(0);
            $table->integer('total_debt');
            $table->integer('repaid_amount')->default(0);
            $table->enum('status', ['active', 'repaid', 'defaulted'])->default('active');
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();
        });

        // ===== Lucky Box =====
        Schema::create('lucky_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('cost');
            $table->string('color')->nullable();
            $table->integer('daily_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lucky_box_prizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lucky_box_id');
            $table->string('name');
            $table->string('type');
            $table->string('value');
            $table->integer('weight');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lucky_box_plays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lucky_box_id')->nullable();
            $table->unsignedBigInteger('prize_id')->nullable();
            $table->integer('points_spent');
            $table->string('result');
            $table->timestamps();
        });

        // ===== Lucky Wheel =====
        Schema::create('lucky_wheels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('spin_mode')->default('daily');
            $table->integer('point_cost');
            $table->integer('daily_limit')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lucky_wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lucky_wheel_id');
            $table->string('name');
            $table->string('type');
            $table->string('value');
            $table->integer('weight');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('lucky_wheel_plays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lucky_wheel_id')->nullable();
            $table->unsignedBigInteger('prize_id')->nullable();
            $table->integer('points_spent');
            $table->string('result');
            $table->timestamps();
        });

        // ===== Sport Events =====
        Schema::create('sport_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('event_type')->nullable();
            $table->string('prediction_type')->nullable();
            $table->boolean('allow_draw')->default(false);
            $table->string('option_a_image')->nullable();
            $table->string('option_b_image')->nullable();
            $table->timestamp('match_date');
            $table->timestamp('prediction_deadline')->nullable();
            $table->integer('entry_fee')->default(0);
            $table->integer('reward_per_winner')->nullable();
            $table->enum('status', ['open', 'closed', 'settled', 'completed', 'cancelled'])->default('open');
            $table->string('winner')->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('rewards_distributed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sport_predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('prediction');
            $table->integer('points_bet');
            $table->boolean('is_winner')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->integer('max_active_events')->nullable();
            $table->integer('min_time_before_deadline')->nullable();
            $table->boolean('allow_prediction_edit')->default(false);
            $table->integer('edit_fee')->nullable();
            $table->boolean('auto_distribute_rewards')->default(true);
            $table->timestamps();
        });

        // ===== Quiz =====
        Schema::create('quiz_contests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('entry_fee')->default(0);
            $table->string('prize')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['pending', 'active', 'finished'])->default('pending');
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contest_id');
            $table->string('question');
            $table->json('options');
            $table->string('correct_answer');
            $table->integer('points')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_id');
            $table->string('answer');
            $table->boolean('is_correct')->nullable();
            $table->integer('points_earned')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        // ===== Notifications & Banners =====
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('image')->nullable();
            $table->string('audience')->default('all');
            $table->json('target_user_ids')->nullable();
            $table->enum('status', ['draft', 'sent', 'cancelled'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // ===== SMS =====
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->text('message');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->string('direction')->default('outgoing');
            $table->string('sender')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        // ===== Settings =====
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->unique(['key', 'group']);
            $table->timestamps();
        });

        // ===== Activity Logs =====
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // ===== Products & Orders =====
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('type')->default('card');
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->string('card_code')->nullable();
            $table->timestamps();
        });

        // ===== Wallet =====
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ===== Absher (Loan) =====
        Schema::create('absher_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedBigInteger('loan_reward_id')->nullable();
            $table->integer('point_cost')->default(0);
            $table->integer('min_points_threshold')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('require_recent_activity')->default(false);
            $table->integer('activity_days')->nullable();
            $table->boolean('auto_reset_points')->default(false);
            $table->date('last_reset_date')->nullable();
            $table->timestamps();
        });

        Schema::create('category_borrow_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_borrowable')->default(false);
            $table->integer('max_borrow_amount')->nullable();
            $table->integer('min_points_threshold')->nullable();
            $table->unique(['category_id']);
            $table->timestamps();
        });

        // ===== Transfer Settings =====
        Schema::create('transfer_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->integer('min_transfer_amount')->nullable();
            $table->integer('max_transfer_amount')->nullable();
            $table->decimal('transfer_fee_percent', 5, 2)->default(0);
            $table->integer('min_balance_required')->nullable();
            $table->boolean('require_phone_verification')->default(false);
            $table->timestamps();
        });

        // ===== OTP Codes =====
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // ===== Feature Toggles =====
        Schema::create('feature_toggles', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique();
            $table->string('label');
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });

        // ===== Device Fingerprints =====
        Schema::create('device_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamps();
        });

        // ===== Channels =====
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stream_url');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ===== Restrictions =====
        Schema::create('restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('reset_type')->default('zero');
            $table->integer('reset_value')->nullable();
            $table->date('last_reset_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'channels', 'device_fingerprints', 'feature_toggles', 'otp_codes',
            'transfer_settings', 'category_borrow_settings', 'absher_settings',
            'wallet_transactions', 'order_items', 'orders', 'products',
            'activity_logs', 'settings', 'sms_messages', 'banners', 'notifications',
            'quiz_answers', 'quiz_questions', 'quiz_contests',
            'prediction_settings', 'sport_predictions', 'sport_events',
            'lucky_wheel_plays', 'lucky_wheel_prizes', 'lucky_wheels',
            'lucky_box_plays', 'lucky_box_prizes', 'lucky_boxes',
            'point_borrows', 'point_transfers', 'point_transactions',
            'card_redemptions', 'network_cards', 'reward_cards', 'rewards',
            'categories', 'sessions', 'password_reset_tokens', 'users', 'admins',
            'restrictions',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};

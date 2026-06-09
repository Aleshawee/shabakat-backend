<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->string('direction', 20)->default('outgoing')->after('status')->comment('outgoing, incoming');
            $table->string('sender', 20)->nullable()->after('phone');
            $table->string('reference_id', 100)->nullable()->after('sender');
            $table->timestamp('received_at')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropColumn(['direction', 'sender', 'reference_id', 'received_at']);
        });
    }
};

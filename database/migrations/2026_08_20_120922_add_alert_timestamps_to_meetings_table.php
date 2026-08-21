<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->timestamp('upcoming_alert_sent_at')->nullable()->after('status');
            $table->timestamp('missed_alert_sent_at')->nullable()->after('upcoming_alert_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['upcoming_alert_sent_at', 'missed_alert_sent_at']);
        });
    }
};

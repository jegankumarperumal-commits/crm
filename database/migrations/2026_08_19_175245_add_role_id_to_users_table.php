<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
        });

        $roleIdBySlug = DB::table('roles')->pluck('id', 'slug');

        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            if (isset($roleIdBySlug[$user->role])) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleIdBySlug[$user->role]]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('business_analyst');
        });

        $slugById = DB::table('roles')->pluck('slug', 'id');

        foreach (DB::table('users')->select('id', 'role_id')->get() as $user) {
            if ($user->role_id !== null && isset($slugById[$user->role_id])) {
                DB::table('users')->where('id', $user->id)->update(['role' => $slugById[$user->role_id]]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};

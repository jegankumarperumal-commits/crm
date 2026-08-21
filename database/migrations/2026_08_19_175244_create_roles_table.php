<?php

use App\Enums\Permission;
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_founder')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        $all = array_map(fn (Permission $permission) => $permission->value, Permission::cases());
        $salesPipeline = [
            Permission::ManageLeads->value,
            Permission::ManageFollowUps->value,
            Permission::ManageMeetings->value,
            Permission::ManageQuotations->value,
            Permission::ManageClientsInvoices->value,
            Permission::ViewReports->value,
        ];

        $now = now();

        DB::table('roles')->insert([
            ['name' => 'Founder / CEO', 'slug' => 'founder', 'is_founder' => true, 'permissions' => json_encode($all), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Business Analyst', 'slug' => 'business_analyst', 'is_founder' => false, 'permissions' => json_encode($salesPipeline), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Web Developer', 'slug' => 'web_developer', 'is_founder' => false, 'permissions' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Graphic Designer', 'slug' => 'graphic_designer', 'is_founder' => false, 'permissions' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Video Editor', 'slug' => 'video_editor', 'is_founder' => false, 'permissions' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Digital Marketing Executive', 'slug' => 'digital_marketing_executive', 'is_founder' => false, 'permissions' => json_encode([Permission::ManageLeads->value, Permission::ViewReports->value]), 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Intern', 'slug' => 'intern', 'is_founder' => false, 'permissions' => json_encode([]), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

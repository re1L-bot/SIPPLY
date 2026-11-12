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
        if (app()->environment('testing')) {
            return;
        }

        if (! Schema::hasTable('migration_admins_table')) {
            Schema::create('migration_admins_table', function (Blueprint $table): void {
                $table->boolean('created_by_migration')->default(false);
            });
        }

        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table): void {
                $table->id('admin_id');
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role', 20)->default('owner');
                $table->string('phone_number', 25)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
            DB::table('migration_admins_table')->insert(['created_by_migration' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('migration_admins_table')) {
            return;
        }

        $createdByMigration = DB::table('migration_admins_table')->value('created_by_migration');
        if ($createdByMigration) {
            Schema::dropIfExists('admins');
        }

        Schema::dropIfExists('migration_admins_table');
    }
};

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
        if (Schema::hasTable('admins') && ! Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->string('role', 20)->default('owner');
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'coupon_code')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('coupon_code', 50)->nullable();
            });
        }

        if (! Schema::hasTable('discount_coupons')) {
            Schema::create('discount_coupons', function (Blueprint $table): void {
                $table->id('coupon_id');
                $table->string('code', 50)->unique();
                $table->string('discount_type', 20);
                $table->decimal('discount_value', 10, 2);
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table): void {
                $table->id('promotion_id');
                $table->string('title', 120);
                $table->text('description');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table): void {
                $table->id('activity_id');
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('action', 80);
                $table->string('entity_type', 80);
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('summary', 500);
                $table->timestamp('created_at')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('discount_coupons');

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'coupon_code')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('coupon_code');
            });
        }

        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->dropColumn('role');
            });
        }
    }
};

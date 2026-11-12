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
        if (! Schema::hasTable('products')) {
            if (app()->environment('testing')) {
                return;
            }

            throw new RuntimeException('The products table must exist before stock can be added.');
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('stock_quantity')->default(5)->after('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'stock_quantity')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('stock_quantity');
        });
    }
};

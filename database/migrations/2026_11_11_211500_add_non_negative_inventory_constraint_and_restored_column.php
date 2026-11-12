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
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'stock_restored_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->timestamp('stock_restored_at')->nullable()->after('placed_at');
            });
        }

        if (Schema::hasTable('products')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $existing = DB::select(
                    "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND CONSTRAINT_NAME = 'chk_products_stock_non_negative'"
                );
                if (empty($existing)) {
                    DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_stock_non_negative CHECK (stock_quantity >= 0)');
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $existing = DB::select(
                    "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND CONSTRAINT_NAME = 'chk_products_stock_non_negative'"
                );
                if (! empty($existing)) {
                    DB::statement('ALTER TABLE products DROP CONSTRAINT chk_products_stock_non_negative');
                }
            }
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'stock_restored_at')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('stock_restored_at');
            });
        }
    }
};

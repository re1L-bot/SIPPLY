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
        if (! Schema::hasTable('products')) {
            if (app()->environment('testing')) {
                return;
            }

            throw new RuntimeException('The products table must exist before sizes and ingredients can be added.');
        }

        if (! Schema::hasTable('migration_product_size_ingredients')) {
            Schema::create('migration_product_size_ingredients', function (Blueprint $table): void {
                $table->boolean('created_product_sizes')->default(false);
                $table->boolean('added_ingredients')->default(false);
            });
        }

        if (! DB::table('migration_product_size_ingredients')->exists()) {
            DB::table('migration_product_size_ingredients')->insert([
                'created_product_sizes' => false,
                'added_ingredients' => false,
            ]);
        }

        if (! Schema::hasTable('product_sizes')) {
            Schema::create('product_sizes', function (Blueprint $table): void {
                $table->id('size_id');
                $table->string('size_name', 50)->unique();
                $table->decimal('price_adjustment', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
            });
            DB::table('migration_product_size_ingredients')->update(['created_product_sizes' => true]);
        }

        if (! Schema::hasColumn('products', 'ingredients')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->text('ingredients')->nullable();
            });
            DB::table('migration_product_size_ingredients')->update(['added_ingredients' => true]);
        }

        foreach ([
            ['size_name' => 'Regular', 'price_adjustment' => 0],
            ['size_name' => 'Large', 'price_adjustment' => 20],
        ] as $defaultSize) {
            if (! DB::table('product_sizes')->where('size_name', $defaultSize['size_name'])->exists()) {
                DB::table('product_sizes')->insert([
                    'size_name' => $defaultSize['size_name'],
                    'price_adjustment' => $defaultSize['price_adjustment'],
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('migration_product_size_ingredients')) {
            return;
        }

        $migrationChanges = DB::table('migration_product_size_ingredients')->first();

        if ($migrationChanges?->added_ingredients && Schema::hasColumn('products', 'ingredients')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('ingredients');
            });
        }

        if ($migrationChanges?->created_product_sizes) {
            Schema::dropIfExists('product_sizes');
        }

        Schema::dropIfExists('migration_product_size_ingredients');
    }
};

<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesMenuTables
{
    protected function createMenuTables(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id('category_id');
            $table->string('category_name', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id('product_id');
            $table->unsignedBigInteger('category_id');
            $table->string('product_name', 180);
            $table->text('description')->nullable();
            $table->text('ingredients')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('stock_quantity')->default(5);
            $table->timestamps();
        });

        Schema::create('product_sizes', function (Blueprint $table): void {
            $table->id('size_id');
            $table->string('size_name', 50)->unique();
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
        });
        DB::table('product_sizes')->insert([
            ['size_name' => 'Regular', 'price_adjustment' => 0, 'is_active' => true],
            ['size_name' => 'Large', 'price_adjustment' => 20, 'is_active' => true],
        ]);

        if (! Schema::hasTable('product_size_prices')) {
            Schema::create('product_size_prices', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('size_id');
                $table->decimal('price', 10, 2);
                $table->timestamps();
            });
        }

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id('image_id');
            $table->unsignedBigInteger('product_id');
            $table->string('image_path', 500);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id');
            $table->string('cart_status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->id('cart_item_id');
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('special_instructions', 500)->nullable();
            $table->timestamps();
        });
    }
}

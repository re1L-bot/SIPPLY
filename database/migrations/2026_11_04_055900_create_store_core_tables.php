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

        if (! Schema::hasTable('migration_store_core_tables')) {
            Schema::create('migration_store_core_tables', function (Blueprint $table): void {
                $table->string('table_name', 100)->primary();
            });
        }

        $createTable = function (string $tableName, Closure $definition): void {
            if (Schema::hasTable($tableName)) {
                return;
            }

            Schema::create($tableName, $definition);
            DB::table('migration_store_core_tables')->insert(['table_name' => $tableName]);
        };

        $createTable('categories', function (Blueprint $table): void {
            $table->id('category_id');
            $table->string('category_name', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $createTable('products', function (Blueprint $table): void {
            $table->id('product_id');
            $table->unsignedBigInteger('category_id');
            $table->string('product_name', 180);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->foreign('category_id')->references('category_id')->on('categories')->restrictOnDelete();
        });

        $createTable('product_images', function (Blueprint $table): void {
            $table->id('image_id');
            $table->unsignedBigInteger('product_id');
            $table->string('image_path', 500);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
        });

        $createTable('carts', function (Blueprint $table): void {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id');
            $table->string('cart_status', 20)->default('active');
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        $createTable('cart_items', function (Blueprint $table): void {
            $table->id('cart_item_id');
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('special_instructions', 500)->nullable();
            $table->timestamps();
            $table->foreign('cart_id')->references('cart_id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->restrictOnDelete();
        });

        $createTable('user_addresses', function (Blueprint $table): void {
            $table->id('address_id');
            $table->unsignedBigInteger('user_id');
            $table->string('label', 50)->nullable();
            $table->string('recipient_name', 200);
            $table->string('phone_number', 25);
            $table->string('address_line', 255);
            $table->string('barangay', 100);
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        $createTable('orders', function (Blueprint $table): void {
            $table->id('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('order_number', 40)->unique();
            $table->string('order_status', 40);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->string('recipient_name', 200);
            $table->string('recipient_phone', 25);
            $table->string('delivery_address', 500);
            $table->string('delivery_notes', 500)->nullable();
            $table->timestamp('placed_at')->index();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('address_id')->references('address_id')->on('user_addresses')->nullOnDelete();
        });

        $createTable('order_items', function (Blueprint $table): void {
            $table->id('order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 180);
            $table->string('size_name', 50)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('product_id')->on('products')->nullOnDelete();
        });

        $createTable('deliveries', function (Blueprint $table): void {
            $table->id('delivery_id');
            $table->unsignedBigInteger('order_id');
            $table->string('delivery_status', 30)->default('pending');
            $table->string('tracking_reference', 100)->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
        });

        $createTable('payment_methods', function (Blueprint $table): void {
            $table->id('payment_method_id');
            $table->string('method_name', 80);
            $table->boolean('is_active')->default(true);
        });

        $createTable('payments', function (Blueprint $table): void {
            $table->id('payment_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('transaction_reference', 150)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('payment_method_id')->on('payment_methods')->restrictOnDelete();
        });

        $createTable('order_status_history', function (Blueprint $table): void {
            $table->id('history_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('status', 40);
            $table->string('notes', 500)->nullable();
            $table->timestamp('changed_at');
            $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('migration_store_core_tables')) {
            return;
        }

        foreach ([
            'order_status_history',
            'payments',
            'deliveries',
            'order_items',
            'orders',
            'user_addresses',
            'cart_items',
            'carts',
            'product_images',
            'products',
            'categories',
            'payment_methods',
        ] as $tableName) {
            if (DB::table('migration_store_core_tables')->where('table_name', $tableName)->exists()) {
                Schema::dropIfExists($tableName);
            }
        }

        Schema::dropIfExists('migration_store_core_tables');
    }
};

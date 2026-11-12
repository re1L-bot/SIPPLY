<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesAccountTables
{
    protected function createAccountTables(): void
    {
        Schema::create('user_addresses', function (Blueprint $table): void {
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
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('customer_email', 254)->nullable();
            $table->string('order_number', 40);
            $table->string('order_status', 40);
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->string('recipient_name', 200);
            $table->string('recipient_phone', 25);
            $table->string('delivery_address', 500);
            $table->string('delivery_notes', 500)->nullable();
            $table->timestamp('placed_at');
            $table->timestamp('stock_restored_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id('order_item_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 180);
            $table->string('size_name', 50)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
        });

        Schema::create('deliveries', function (Blueprint $table): void {
            $table->id('delivery_id');
            $table->unsignedBigInteger('order_id');
            $table->string('delivery_status', 30)->default('pending');
            $table->string('tracking_reference', 100)->nullable();
            $table->timestamps();
        });
    }
}

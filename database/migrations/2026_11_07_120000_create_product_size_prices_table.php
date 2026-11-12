<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_size_prices')) {
            Schema::create('product_size_prices', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('size_id');
                $table->decimal('price', 10, 2);
                $table->timestamps();

                $table->foreign('product_id')->references('product_id')->on('products')->cascadeOnDelete();
                $table->foreign('size_id')->references('size_id')->on('product_sizes')->cascadeOnDelete();
                $table->unique(['product_id', 'size_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_size_prices');
    }
};

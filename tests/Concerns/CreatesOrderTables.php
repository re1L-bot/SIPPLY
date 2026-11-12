<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesOrderTables
{
    protected function createOrderTables(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id('payment_method_id');
            $table->string('method_name', 80);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id('payment_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('transaction_reference', 150)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table): void {
            $table->id('history_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('status', 40);
            $table->string('notes', 500)->nullable();
            $table->timestamp('changed_at');
        });
    }
}

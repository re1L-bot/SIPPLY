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
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email', 254)->nullable()->after('user_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        if (Schema::hasTable('users') && DB::table('orders')->whereNull('customer_email')->whereNotNull('user_id')->exists()) {
            DB::table('orders')
                ->whereNull('customer_email')
                ->whereNotNull('user_id')
                ->orderBy('order_id')
                ->chunkById(500, function ($orders): void {
                    $emails = DB::table('users')
                        ->whereIn('user_id', $orders->pluck('user_id')->unique())
                        ->pluck('email', 'user_id');
                    foreach ($orders as $order) {
                        if (isset($emails[$order->user_id])) {
                            DB::table('orders')->where('order_id', $order->order_id)->update([
                                'customer_email' => $emails[$order->user_id],
                            ]);
                        }
                    }
                }, 'order_id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'customer_email')) {
            return;
        }

        if (DB::table('orders')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Guest orders exist. Remove or associate them before rolling back guest checkout.');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_email']);
            $table->dropColumn('customer_email');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};

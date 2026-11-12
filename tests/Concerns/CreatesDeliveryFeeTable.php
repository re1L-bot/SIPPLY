<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesDeliveryFeeTable
{
    protected function createDeliveryFeeTable(): void
    {
        if (! Schema::hasTable('delivery_fees')) {
            Schema::create('delivery_fees', function (Blueprint $table): void {
                $table->id('delivery_fee_id');
                $table->string('municipality', 50)->unique();
                $table->decimal('fee', 10, 2);
                $table->timestamps();
            });
        } else {
            DB::table('delivery_fees')->delete();
        }

        $now = now();
        DB::table('delivery_fees')->insert([
            ['municipality' => 'Bantayan', 'fee' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['municipality' => 'Madridejos', 'fee' => 300, 'created_at' => $now, 'updated_at' => $now],
            ['municipality' => 'Sta. Fe', 'fee' => 200, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}

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
        Schema::create('delivery_fees', function (Blueprint $table): void {
            $table->id('delivery_fee_id');
            $table->string('municipality', 50)->unique();
            $table->decimal('fee', 10, 2);
            $table->timestamps();
        });

        $now = now();
        DB::table('delivery_fees')->insert([
            ['municipality' => 'Bantayan', 'fee' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['municipality' => 'Madridejos', 'fee' => 300, 'created_at' => $now, 'updated_at' => $now],
            ['municipality' => 'Sta. Fe', 'fee' => 200, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};

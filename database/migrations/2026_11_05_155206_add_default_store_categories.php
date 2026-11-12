<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            if (app()->environment('testing')) {
                return;
            }

            throw new RuntimeException('The categories table must exist before default store categories can be added.');
        }

        $now = now();

        foreach ([
            'Coffee' => 'Freshly prepared coffee favorites.',
            'Milk Tea' => 'Creamy milk tea favorites made fresh.',
            'Pastries' => 'Freshly baked treats to enjoy with your drink.',
        ] as $categoryName => $description) {
            $category = DB::table('categories')
                ->whereRaw('LOWER(category_name) = ?', [mb_strtolower($categoryName)])
                ->first(['category_id']);

            if ($category === null) {
                DB::table('categories')->insert([
                    'category_name' => $categoryName,
                    'description' => $description,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('categories')
                ->where('category_id', $category->category_id)
                ->update([
                    'category_name' => $categoryName,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep these baseline categories because products may have been assigned to them.
    }
};

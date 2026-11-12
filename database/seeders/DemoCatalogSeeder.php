<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Seed a small local catalog suitable for demonstrations and assessment testing.
     */
    public function run(): void
    {
        $catalog = [
            'Milk Tea' => [
                ['Brown Sugar Milk Tea', 'Black tea, milk, and brown sugar.', 89, 12, true],
                ['Classic Pearl Milk Tea', 'Classic milk tea with chewy pearls.', 79, 10, true],
                ['Matcha Cream Milk Tea', 'Earthy matcha with a smooth cream finish.', 99, 0, true],
                ['Wintermelon Milk Tea', 'Sweet wintermelon with fresh milk.', 85, 8, false],
            ],
            'Pastries' => [
                ['Butter Croissant', 'A flaky, buttery pastry baked until golden.', 65, 9, true],
                ['Chocolate Muffin', 'A soft chocolate muffin with a rich cocoa flavor.', 58, 7, true],
                ['Cinnamon Roll', 'A tender pastry rolled with cinnamon sugar.', 72, 6, true],
            ],
            'Coffee' => [
                ['Iced Spanish Latte', 'Espresso, milk, and a touch of sweetness.', 99, 8, true],
                ['Caramel Cold Brew', 'Slow-steeped coffee with caramel.', 105, 5, true],
                ['Mocha Oat Latte', 'Chocolate and espresso with oat milk.', 115, 4, true],
            ],
        ];
        $now = now();

        foreach ($catalog as $categoryName => $products) {
            DB::table('categories')->updateOrInsert(
                ['category_name' => $categoryName],
                [
                    'description' => 'Freshly prepared '.strtolower($categoryName).' favorites.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
            $categoryId = DB::table('categories')->where('category_name', $categoryName)->value('category_id');

            foreach ($products as [$name, $description, $price, $stock, $available]) {
                DB::table('products')->updateOrInsert(
                    ['product_name' => $name],
                    [
                        'category_id' => $categoryId,
                        'description' => $description,
                        'base_price' => $price,
                        'stock_quantity' => $stock,
                        'is_available' => $available,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }

        foreach (['Cash on Delivery', 'GCash'] as $methodName) {
            DB::table('payment_methods')->updateOrInsert(
                ['method_name' => $methodName],
                ['is_active' => true],
            );
        }
    }
}

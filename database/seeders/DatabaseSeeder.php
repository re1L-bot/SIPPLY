<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        if (Schema::hasTable('users')) {
            User::query()->firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'first_name' => 'Demo',
                    'last_name' => 'Customer',
                    'password' => 'password',
                    'phone_number' => '09170000000',
                    'is_active' => true,
                ],
            );
        }

        if (Schema::hasTable('admins')) {
            Admin::query()->firstOrCreate(
                ['email' => 'owner@example.test'],
                [
                    'first_name' => 'Demo',
                    'last_name' => 'Owner',
                    'password' => 'password',
                    'role' => 'owner',
                    'is_active' => true,
                ],
            );
        }

        if (Schema::hasTable('categories') && Schema::hasTable('products') && Schema::hasTable('payment_methods')) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}

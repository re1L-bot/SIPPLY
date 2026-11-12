<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesAdminTables
{
    protected function createAdminTables(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id('admin_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 20)->default('owner');
            $table->string('phone_number', 25)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }
}

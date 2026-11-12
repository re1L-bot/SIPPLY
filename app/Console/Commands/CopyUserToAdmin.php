<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('admin:copy-user-to-admin {email : Email address of the existing customer account}')]
#[Description('Copy an existing active customer account into the separate admins table')]
class CopyUserToAdmin extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = DB::table('users')->where('email', $email)->first();

        if ($user === null) {
            $this->components->error('No customer account exists with that email address.');

            return self::FAILURE;
        }

        if (! $user->is_active) {
            $this->components->error('An inactive customer account cannot be copied to administrators.');

            return self::FAILURE;
        }

        if (DB::table('admins')->where('email', $user->email)->exists()) {
            $this->components->error('An administrator with that email address already exists.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user): void {
            DB::table('admins')->insert([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'password' => $user->password,
                'phone_number' => $user->phone_number,
                'is_active' => $user->is_active,
                'last_login_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->components->info("Copied {$user->email} into the admins table. The administrator can sign in with the same password.");

        return self::SUCCESS;
    }
}

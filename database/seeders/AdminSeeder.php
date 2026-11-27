<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /** Jalankan: php artisan db:seed --class=AdminSeeder  (isi ADMIN_EMAIL & ADMIN_PASSWORD di .env dulu) */
    public function run(): void
    {
        $email    = config('shop.admin_email');
        $password = config('shop.admin_password');

        if (! $email || ! $password) {
            $this->command->error('Isi ADMIN_EMAIL dan ADMIN_PASSWORD di file .env terlebih dulu.');
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name     = $user->name ?: 'Admin';
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();

        $this->command->info("Admin siap: {$email}");
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach (['admin' => 'SMS Administrator', 'teacher' => 'Demo Teacher', 'student' => 'Demo Student'] as $role => $name) {
            User::firstOrCreate(['email' => $role.'@example.test'], [
                'name' => $name,
                'password' => config('sms.demo_password') ?: Str::password(32),
                'role' => $role,
                'status' => true,
            ]);
        }
    }
}

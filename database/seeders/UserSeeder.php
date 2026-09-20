<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(

            [
                'email' => 'adityapratap7391@gmail.com',
            ],

            [
                'name' => 'SMS Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'status' => true,
            ]

        );


        User::updateOrCreate(

            [
                'email' => 'teacher@sms.com',
            ],

            [
                'name' => 'Demo Teacher',
                'password' => Hash::make('Teacher@123'),
                'role' => 'teacher',
                'status' => true,
            ]

        );


        User::updateOrCreate(

            [
                'email' => 'student@sms.com',
            ],

            [
                'name' => 'Demo Student',
                'password' => Hash::make('Student@123'),
                'role' => 'student',
                'status' => true,
            ]

        );
    }
}
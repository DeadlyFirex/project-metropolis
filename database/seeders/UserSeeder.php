<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin'),
            ],
            [
                'name' => 'Architect Alex',
                'email' => 'alex@architect.com',
                'password' => Hash::make('alex'),
            ],
        ]);
    }
}

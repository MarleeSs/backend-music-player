<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'full_name' => 'User 2',
            'email' => 'user2@coba.com',
            'password' => 'user',
            'role' => 'user',
            'image' => 'default.png',
        ]);
    }
}

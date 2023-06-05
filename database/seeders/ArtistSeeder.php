<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Artist::create([
            'full_name' => 'Artist',
            'email' => 'artist@coba.com',
            'password' => 'artist',
            'image' => 'default.png',
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Fixes "Class not found"
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // firstOrCreate ensures we don't duplicate or override existing email records
        User::firstOrCreate(
    ['email' => 'admin@example.com'], 
    [
        'first'    => 'Admin',
        'last'     => 'User',
        'password' => 'admin123', // DO NOT use Hash::make() here
    ]
);

    }
}

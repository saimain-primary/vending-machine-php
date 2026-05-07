<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@vending.test',
            'password' => Hash::make('password'),
        ]);

        User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer@vending.test',
            'password' => Hash::make('password'),
        ]);

        $this->call(ProductSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya menyisakan 1 Akun Supervisor untuk rilis
        DB::table('users')->insert([
            'name' => 'Fuad Dzakiat',
            'username' => 'fuaddzakiat',
            'password' => Hash::make('password123'), // Password default: password
            'role' => 'supervisor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
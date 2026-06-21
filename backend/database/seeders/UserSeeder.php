<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Laboran
        User::updateOrCreate(
            ['email' => 'laboran@school.id'],
            [
                'name' => 'Laboran',
                'password' => Hash::make('password123'),
                'role' => 'Laboran',
            ]
        );

        // 2. Guru
        User::updateOrCreate(
            ['email' => 'guru@school.id'],
            [
                'name' => 'Guru',
                'password' => Hash::make('password123'),
                'role' => 'Guru',
            ]
        );

        // 3. Siswa
        User::updateOrCreate(
            ['email' => 'siswa@school.id'],
            [
                'name' => 'Siswa',
                'password' => Hash::make('password123'),
                'role' => 'Siswa',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal aplikasi SupplyGuard.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator SupplyGuard',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'User SupplyGuard',
                'password' => Hash::make('User@12345'),
                'role' => 'user',
            ]
        );

        $this->call([
            PortSeeder::class,
            ArticleSeeder::class,
            SentimentWordSeeder::class,
        ]);
    }
}
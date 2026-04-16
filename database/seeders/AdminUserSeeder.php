<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]
        );

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
    }
}

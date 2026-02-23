<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@tokenapp.com'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'email' => 'admin@tokenapp.com',
                'password' => Hash::make('1234567'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@tokenapp.com');
        //$this->command->info(`Password: ${password}`);
        $this->command->warn('Please change the password after first login!');
    }
}

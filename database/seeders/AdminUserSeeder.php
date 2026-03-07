<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'web.hrm@hartonomotor.com';
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            \App\Models\User::create([
                'name' => 'IT Admin',
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('admin987'),
                'role' => 'admin',
            ]);
            $this->command->info('Admin user created successfully.');
        } else {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make('admin987'),
                'role' => 'admin',
            ]);
            $this->command->info('Admin user password updated.');
        }
    }
}

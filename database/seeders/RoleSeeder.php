<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles
        $roles = ['admin', 'hr', 'user'];

        // Create roles
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // Define users with their roles
        $users = [
            [
                'name' => 'Malik',
                'email' => 'hr@gmail.com',
                'role' => 'admin',
            ],
            [
                'name' => 'bryann',
                'email' => 'bryn@gmail.com',
                'role' => 'hr',
            ],
        ];

        // Create users and assign roles
        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($userData['role']);
        }
    }
}

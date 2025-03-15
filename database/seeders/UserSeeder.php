<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@malinator.com',
            'password' => Hash::make('123456789'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        // Create 9 regular users
        $faker = Faker::create();
        for ($i = 0; $i < 9; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($userRole);
        }
    }
}

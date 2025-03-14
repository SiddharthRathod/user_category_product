<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assign role to a user (Example: Assign Admin role to first user)
        $admin = User::first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}

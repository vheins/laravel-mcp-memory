<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin Role
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        // 2. Create Developer User
        $user = User::firstOrCreate(
            ['email' => 'developer@memory-mcp.id'],
            [
                'name' => 'Super Developer',
                'password' => Hash::make('dev-root'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign Role
        $user->assignRole($role);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@pos.com'],
            [
                'name'      => 'Super Admin',
                'email'     => 'superadmin@pos.com',
                'password'  => Hash::make('superadmin@1234'),
                'role'      => 'superadmin',
                'is_active' => true,
            ]
        );
    }
}
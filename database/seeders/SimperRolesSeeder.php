<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SimperRolesSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'full_name' => 'Administrator Utama',
                'email' => 'admin@lcm.co.id',
                'password_hash' => Hash::make('super_admin_lcm'),
                'role' => 'admin',
                'is_active' => true,
                'must_change_password' => false,
            ],
            [
                'full_name' => 'Admin HRGA',
                'email' => 'hrga@lcm.co.id',
                'password_hash' => Hash::make('hrga_lcm_secure'),
                'role' => 'hrga',
                'is_active' => true,
                'must_change_password' => false,
            ],
            [
                'full_name' => 'Admin TOD',
                'email' => 'tod@lcm.co.id',
                'password_hash' => Hash::make('tod_lcm_secure'),
                'role' => 'tod',
                'is_active' => true,
                'must_change_password' => false,
            ],
            [
                'full_name' => 'Admin SHE',
                'email' => 'she@lcm.co.id',
                'password_hash' => Hash::make('she_lcm_secure'),
                'role' => 'she',
                'is_active' => true,
                'must_change_password' => false,
            ],
            [
                'full_name' => 'Mitra Subcon',
                'email' => 'subcon@lcm.co.id',
                'password_hash' => Hash::make('subcon_lcm_secure'),
                'role' => 'subcon',
                'is_active' => true,
                'must_change_password' => false,
            ],
            [
                'full_name' => 'Paramedic Reviewer',
                'email' => 'paramedic@lcm.co.id',
                'password_hash' => Hash::make('paramedic_lcm_secure'),
                'role' => 'paramedic',
                'is_active' => true,
                'must_change_password' => false,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MobileTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'           => 'Maria Santos',
                'username'       => 'msantos',
                'email'          => 'msantos@bsu.edu.ph',
                'position_title' => 'Dean',
                'office_code'    => 'COE',
                'role_code'      => 'office-head',
            ],
            [
                'name'           => 'Juan dela Cruz',
                'username'       => 'jdelacruz',
                'email'          => 'jdelacruz@bsu.edu.ph',
                'position_title' => 'Dean',
                'office_code'    => 'CBA',
                'role_code'      => 'office-head',
            ],
            [
                'name'           => 'Dr. Jose Reyes',
                'username'       => 'jreyes',
                'email'          => 'jreyes@bsu.edu.ph',
                'position_title' => 'Vice Chancellor',
                'office_code'    => 'OVC',
                'role_code'      => 'vice-chancellor',
            ],
            [
                'name'           => 'Ana Garcia',
                'username'       => 'agarcia',
                'email'          => 'agarcia@bsu.edu.ph',
                'position_title' => 'Budget Officer',
                'office_code'    => 'FIN',
                'role_code'      => 'finance-office',
            ],
            [
                'name'           => 'Admin User',
                'username'       => 'ictadmin',
                'email'          => 'admin@bsu.edu.ph',
                'position_title' => 'System Administrator',
                'office_code'    => 'CICS',
                'role_code'      => 'system-admin',
            ],
        ];

        foreach ($users as $data) {
            $office = Office::where('code', $data['office_code'])->firstOrFail();
            $role   = Role::where('code', $data['role_code'])->firstOrFail();

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'             => $data['name'],
                    'username'         => $data['username'],
                    'office_id'        => $office->id,
                    'position_title'   => $data['position_title'],
                    'password'         => Hash::make('prism2025'),
                    'account_status'   => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $user->roles()->syncWithoutDetaching([
                $role->id => ['assigned_at' => now()],
            ]);

            $user->officeAssignments()->syncWithoutDetaching([
                $office->id => [
                    'role_in_office' => $role->name,
                    'starts_on'      => now()->toDateString(),
                    'is_primary'     => true,
                ],
            ]);
        }
    }
}

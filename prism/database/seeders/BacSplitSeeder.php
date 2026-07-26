<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Adds three named BAC demo accounts (Member, Vice Chairperson, Chairperson)
 * on top of the single generic 'bac' demo user — a login convenience only,
 * mirroring VcSplitSeeder's demo-account pattern. All three still share the
 * generic 'bac' role: AOC's three BAC-owned stages (at_bac_member,
 * at_bac_vice_chair, at_bac_chair) have no per-position authorization split,
 * so any BAC-role user can act at any of the three stages — same as before.
 * Safe to run against an already-seeded database.
 */
class BacSplitSeeder extends Seeder
{
    public function run(): void
    {
        $role   = Role::where('code', 'bac')->first();
        $office = Office::where('code', 'BAC')->first();

        if (!$role || !$office) {
            return;
        }

        collect([
            [
                'name'           => 'BAC Member Demo',
                'username'       => 'bac_member_demo',
                'email'          => 'bac.member.demo@prism.test',
                'position_title' => 'BAC Member',
            ],
            [
                'name'           => 'BAC Vice Chairperson Demo',
                'username'       => 'bac_vc_demo',
                'email'          => 'bac.vc.demo@prism.test',
                'position_title' => 'BAC Vice Chairperson',
            ],
            [
                'name'           => 'BAC Chairperson Demo',
                'username'       => 'bac_chair_demo',
                'email'          => 'bac.chair.demo@prism.test',
                'position_title' => 'BAC Chairperson',
            ],
        ])->each(function (array $demoUser) use ($role, $office) {
            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name'              => $demoUser['name'],
                    'username'          => $demoUser['username'],
                    'office_id'         => $office->id,
                    'position_title'    => $demoUser['position_title'],
                    'password'          => Hash::make('password'),
                    'account_status'    => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $user->roles()->syncWithoutDetaching([
                $role->id => ['assigned_at' => now()],
            ]);

            $user->officeAssignments()->syncWithoutDetaching([
                $office->id => [
                    'role_in_office' => $demoUser['position_title'],
                    'starts_on'      => now()->toDateString(),
                    'is_primary'     => true,
                ],
            ]);
        });
    }
}

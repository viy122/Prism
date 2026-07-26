<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Splits the single "Vice Chancellor" role into VCAA / VCAF for signing
 * purposes, via a lightweight `users.vc_type` column rather than new roles —
 * see the "Split Vice Chancellor" plan for the full rationale. Safe to run
 * against an already-seeded database: only sets vc_type on the two real VCs
 * who actually sign procurement documents, and creates two new demo users.
 */
class VcSplitSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'ljbuenas@bsu.edu.ph')->update(['vc_type' => 'vcaa']);
        User::where('email', 'jvergara@bsu.edu.ph')->update(['vc_type' => 'vcaf']);

        $role = Role::where('code', 'vice-chancellor')->first();
        $office = Office::where('code', 'OVC')->first();

        if (!$role || !$office) {
            return;
        }

        collect([
            [
                'name'     => 'VCAA Demo',
                'username' => 'vcaa_demo',
                'email'    => 'vcaa.demo@prism.test',
                'position_title' => 'Vice Chancellor for Academic Affairs',
                'vc_type'  => 'vcaa',
            ],
            [
                'name'     => 'VCAF Demo',
                'username' => 'vcaf_demo',
                'email'    => 'vcaf.demo@prism.test',
                'position_title' => 'Vice Chancellor for Administration and Finance',
                'vc_type'  => 'vcaf',
            ],
        ])->each(function (array $demoUser) use ($role, $office) {
            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name'           => $demoUser['name'],
                    'username'       => $demoUser['username'],
                    'office_id'      => $office->id,
                    'position_title' => $demoUser['position_title'],
                    'vc_type'        => $demoUser['vc_type'],
                    'password'       => Hash::make('password'),
                    'account_status' => 'active',
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
        });
    }
}

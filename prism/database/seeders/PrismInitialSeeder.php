<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrismInitialSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['code' => 'view_dashboard', 'name' => 'View dashboards'],
            ['code' => 'manage_budget_proposals', 'name' => 'Manage budget proposals'],
            ['code' => 'run_market_scoping', 'name' => 'Run market scoping'],
            ['code' => 'review_budget_proposals', 'name' => 'Review budget proposals'],
            ['code' => 'consolidate_app', 'name' => 'Consolidate Annual Procurement Plan'],
            ['code' => 'upload_purchase_requests', 'name' => 'Upload purchase requests'],
            ['code' => 'track_procurement', 'name' => 'Track procurement'],
            ['code' => 'manage_users', 'name' => 'Manage users and roles'],
            ['code' => 'view_audit_logs', 'name' => 'View audit logs'],
            ['code' => 'sign_documents', 'name' => 'Sign procurement documents'],
            ['code' => 'process_payments', 'name' => 'Process and release payments'],
        ])->mapWithKeys(fn (array $permission) => [
            $permission['code'] => Permission::updateOrCreate(
                ['code' => $permission['code']],
                ['name' => $permission['name']]
            ),
        ]);

        $roles = collect([
            'system-admin' => [
                'name' => 'System Administrator',
                'permissions' => $permissions->keys()->all(),
            ],
            'office-head' => [
                'name' => 'Office Head / Dean',
                'permissions' => ['view_dashboard', 'manage_budget_proposals', 'run_market_scoping', 'upload_purchase_requests'],
            ],
            'finance-office' => [
                'name' => 'Finance Office',
                'permissions' => ['view_dashboard', 'review_budget_proposals', 'consolidate_app'],
            ],
            'procurement-office' => [
                'name' => 'Procurement Office',
                'permissions' => ['view_dashboard', 'track_procurement', 'upload_purchase_requests'],
            ],
            'chancellor' => [
                'name' => 'Chancellor',
                'permissions' => ['view_dashboard', 'review_budget_proposals', 'sign_documents'],
            ],
            'vice-chancellor' => [
                'name' => 'Vice Chancellor',
                'permissions' => ['view_dashboard', 'track_procurement', 'sign_documents'],
            ],
            'accounting-office' => [
                'name' => 'Accounting Office',
                'permissions' => ['view_dashboard', 'track_procurement', 'sign_documents'],
            ],
            'bac' => [
                'name' => 'BAC',
                'permissions' => ['view_dashboard', 'track_procurement', 'sign_documents'],
            ],
            'cashier' => [
                'name' => 'Cashier',
                'permissions' => ['view_dashboard', 'process_payments'],
            ],
        ])->mapWithKeys(function (array $role, string $code) use ($permissions) {
            $model = Role::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $role['name'],
                    'description' => 'Default PRISM role for '.$role['name'].'.',
                    'is_system' => true,
                ]
            );

            $model->permissions()->sync(
                collect($role['permissions'])
                    ->map(fn (string $permissionCode) => $permissions[$permissionCode]->id)
                    ->all()
            );

            return [$code => $model];
        });

        $campus = Campus::updateOrCreate(
            ['code' => 'BSU-ARASOF-NASUGBU'],
            [
                'name' => 'Batangas State University TNEU ARASOF-Nasugbu Campus',
                'address' => 'R. Martinez Street, Nasugbu, Batangas',
                'status' => 'active',
            ]
        );

        $offices = collect([
            'CICS' => ['name' => 'College of Informatics and Computing Sciences', 'office_type' => 'college'],
            'COE'  => ['name' => 'College of Engineering',                          'office_type' => 'college'],
            'CBA'  => ['name' => 'College of Business Administration',               'office_type' => 'college'],
            'FIN'  => ['name' => 'Finance Office',                                   'office_type' => 'administrative'],
            'PROC' => ['name' => 'Procurement Office',                               'office_type' => 'administrative'],
            'OC'   => ['name' => 'Office of the Chancellor',                         'office_type' => 'executive'],
            'OVC'  => ['name' => 'Office of the Vice Chancellor',                    'office_type' => 'executive'],
            'ACCT' => ['name' => 'Accounting Office',                                'office_type' => 'administrative'],
            'BAC'  => ['name' => 'Bids and Awards Committee',                        'office_type' => 'administrative'],
            'CASH' => ['name' => 'Cashier Office',                                   'office_type' => 'administrative'],
        ])->mapWithKeys(fn (array $office, string $code) => [
            $code => Office::updateOrCreate(
                ['code' => $code],
                [
                    'campus_id' => $campus->id,
                    'name' => $office['name'],
                    'office_type' => $office['office_type'],
                    'status' => 'active',
                ]
            ),
        ]);

        collect([
            [
                'name' => 'PRISM Administrator',
                'username' => 'admin',
                'email' => 'admin@prism.test',
                'position_title' => 'System Administrator',
                'office' => 'PROC',
                'role' => 'system-admin',
            ],
            [
                'name' => 'Office Head Demo',
                'username' => 'office_head',
                'email' => 'office.head@prism.test',
                'position_title' => 'Dean',
                'office' => 'CICS',
                'role' => 'office-head',
            ],
            [
                'name' => 'Finance Office Demo',
                'username' => 'finance',
                'email' => 'finance.office@prism.test',
                'position_title' => 'Finance Reviewer',
                'office' => 'FIN',
                'role' => 'finance-office',
            ],
            [
                'name' => 'Procurement Office Demo',
                'username' => 'procurement',
                'email' => 'procurement.office@prism.test',
                'position_title' => 'Procurement Personnel',
                'office' => 'PROC',
                'role' => 'procurement-office',
            ],
            [
                'name' => 'Chancellor Demo',
                'username' => 'chancellor',
                'email' => 'chancellor@prism.test',
                'position_title' => 'Chancellor',
                'office' => 'OC',
                'role' => 'chancellor',
            ],
            [
                'name' => 'Vice Chancellor Demo',
                'username' => 'vice_chancellor',
                'email' => 'vice.chancellor@prism.test',
                'position_title' => 'Vice Chancellor',
                'office' => 'OVC',
                'role' => 'vice-chancellor',
            ],
            [
                'name' => 'Accounting Office Demo',
                'username' => 'accounting',
                'email' => 'accounting.office@prism.test',
                'position_title' => 'Accountant',
                'office' => 'ACCT',
                'role' => 'accounting-office',
            ],
            [
                'name' => 'BAC Demo',
                'username' => 'bac',
                'email' => 'bac@prism.test',
                'position_title' => 'BAC Member',
                'office' => 'BAC',
                'role' => 'bac',
            ],
            [
                'name' => 'Cashier Demo',
                'username' => 'cashier',
                'email' => 'cashier@prism.test',
                'position_title' => 'Cashier',
                'office' => 'CASH',
                'role' => 'cashier',
            ],
            [
                'name' => 'COE Office Head Demo',
                'username' => 'coe_head',
                'email' => 'coe.head@prism.test',
                'position_title' => 'Dean, College of Engineering',
                'office' => 'COE',
                'role' => 'office-head',
            ],
            [
                'name' => 'CBA Office Head Demo',
                'username' => 'cba_head',
                'email' => 'cba.head@prism.test',
                'position_title' => 'Dean, College of Business Administration',
                'office' => 'CBA',
                'role' => 'office-head',
            ],
        ])->each(function (array $demoUser) use ($offices, $roles) {
            $office = $offices[$demoUser['office']];
            $role = $roles[$demoUser['role']];

            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'username' => $demoUser['username'],
                    'office_id' => $office->id,
                    'position_title' => $demoUser['position_title'],
                    'password' => Hash::make('password'),
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
                    'starts_on' => now()->toDateString(),
                    'is_primary' => true,
                ],
            ]);
        });
    }
}

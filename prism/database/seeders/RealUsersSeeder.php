<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RealUsersSeeder extends Seeder
{
    public function run(): void
    {
        $campus = Campus::where('code', 'BSU-ARASOF-NASUGBU')->firstOrFail();

        // ── New offices ──────────────────────────────────────────────────────────
        $newOffices = [
            'CAS'    => ['name' => 'College of Arts and Sciences',                    'office_type' => 'college'],
            'CCJE'   => ['name' => 'College of Criminal Justice Education',            'office_type' => 'college'],
            'CHS'    => ['name' => 'College of Health Sciences',                       'office_type' => 'college'],
            'CTE'    => ['name' => 'College of Teacher Education',                     'office_type' => 'college'],
            'LS'     => ['name' => 'Laboratory School',                                'office_type' => 'academic'],
            'HRMO'   => ['name' => 'Human Resource Management Office',                 'office_type' => 'administrative'],
            'BUD'    => ['name' => 'Budget Office',                                    'office_type' => 'administrative'],
            'PFM'    => ['name' => 'Project and Facility Management Office',           'office_type' => 'administrative'],
            'PS'     => ['name' => 'Property and Supply Office',                       'office_type' => 'administrative'],
            'RMO'    => ['name' => 'Records Management Office',                        'office_type' => 'administrative'],
            'GS'     => ['name' => 'General Services Office',                          'office_type' => 'administrative'],
            'EMU'    => ['name' => 'Environmental Management Unit',                    'office_type' => 'administrative'],
            'ICTS'   => ['name' => 'ICT Services Office',                              'office_type' => 'administrative'],
            'EXT'    => ['name' => 'Extension Services Office',                        'office_type' => 'administrative'],
            'RES'    => ['name' => 'Research Office',                                  'office_type' => 'administrative'],
            'OC-IAS' => ['name' => 'Internal Audit Services',                          'office_type' => 'administrative'],
            'RS'     => ['name' => 'Registration Services',                            'office_type' => 'administrative'],
            'SDS'    => ['name' => 'Student Discipline and Services',                  'office_type' => 'administrative'],
            'LIB'    => ['name' => 'Library Services',                                 'office_type' => 'administrative'],
            'NSTP'   => ['name' => 'National Service Training Program',                'office_type' => 'administrative'],
            'CULT'   => ['name' => 'Culture and Arts Office',                          'office_type' => 'administrative'],
            'SPORTS' => ['name' => 'Sports Development Program',                       'office_type' => 'administrative'],
            'SCHOL'  => ['name' => 'Scholarship and Financial Assistance',             'office_type' => 'administrative'],
            'HLTHO'  => ['name' => 'Health Services Office',                           'office_type' => 'administrative'],
            'SCILAB' => ['name' => 'Science Laboratories',                             'office_type' => 'academic'],
            'PD'     => ['name' => 'Planning and Development Office',                  'office_type' => 'administrative'],
            'EA'     => ['name' => 'External Affairs Office',                          'office_type' => 'administrative'],
            'RG'     => ['name' => 'Resource Generation Office',                       'office_type' => 'administrative'],
            'AJPO'   => ['name' => 'Alumni and Job Placement Office',                  'office_type' => 'administrative'],
        ];

        $offices = collect($newOffices)->mapWithKeys(fn (array $data, string $code) => [
            $code => Office::updateOrCreate(
                ['code' => $code],
                [
                    'campus_id'   => $campus->id,
                    'name'        => $data['name'],
                    'office_type' => $data['office_type'],
                    'status'      => 'active',
                ]
            ),
        ]);

        // Also load existing offices so we can reference them by code
        $existingCodes = ['CICS', 'COE', 'CBA', 'FIN', 'PROC', 'OC', 'OVC', 'ACCT', 'BAC', 'CASH'];
        foreach ($existingCodes as $code) {
            $offices[$code] = Office::where('code', $code)->firstOrFail();
        }

        // ── Role models ───────────────────────────────────────────────────────────
        $roles = collect([
            'system-admin', 'office-head', 'finance-office', 'procurement-office',
            'chancellor', 'vice-chancellor', 'accounting-office', 'bac', 'cashier',
        ])->mapWithKeys(fn (string $code) => [
            $code => Role::where('code', $code)->firstOrFail(),
        ]);

        // ── User definitions ──────────────────────────────────────────────────────
        // Format: name, email, position_title, office_code, role_code
        $users = [
            // Chancellor
            ['name' => 'Dr. Enrico M. Dalangin',           'email' => 'edalangin@bsu.edu.ph',    'position_title' => 'Chancellor',                                        'office' => 'OC',     'role' => 'chancellor'],

            // Vice Chancellors
            ['name' => 'Dr. Lorissa Joana E. Buenas',      'email' => 'ljbuenas@bsu.edu.ph',     'position_title' => 'Vice Chancellor for Academic Affairs / Dean, CICS',   'office' => 'CICS',   'role' => 'vice-chancellor'],
            ['name' => 'Dr. Froilan G. Destreza',           'email' => 'fdestreza@bsu.edu.ph',    'position_title' => 'Vice Chancellor for Research, Development and Extension Services', 'office' => 'OVC', 'role' => 'vice-chancellor'],
            ['name' => 'Mrs. Josephine D. Vergara',         'email' => 'jvergara@bsu.edu.ph',     'position_title' => 'Vice Chancellor for Administration and Finance',      'office' => 'OVC',    'role' => 'vice-chancellor'],
            ['name' => 'Asst. Prof. Lorenjane E. Balan',   'email' => 'lbalan@bsu.edu.ph',       'position_title' => 'Vice Chancellor for Development and External Affairs', 'office' => 'OVC',    'role' => 'vice-chancellor'],

            // Office Heads — Deans
            ['name' => 'Dr. Marvin C. Hernandez',           'email' => 'mhernandez@bsu.edu.ph',   'position_title' => 'Dean, College of Accountancy, Business, Economics and International Hospitality Management', 'office' => 'CBA',    'role' => 'office-head'],
            ['name' => 'Assoc. Prof. Mayette A. Cananea',  'email' => 'mcananea@bsu.edu.ph',     'position_title' => 'Associate Dean, CABEIHM / Head, ETEEAP',             'office' => 'CBA',    'role' => 'office-head'],
            ['name' => 'Dr. Maria Luisa A. Valdez',         'email' => 'mlvaldez@bsu.edu.ph',     'position_title' => 'Dean, College of Arts and Sciences',                  'office' => 'CAS',    'role' => 'office-head'],
            ['name' => 'Mr. Richard John B. Encarnacion',  'email' => 'rjencarnacion@bsu.edu.ph','position_title' => 'OIC-Associate Dean, College of Arts and Sciences',    'office' => 'CAS',    'role' => 'office-head'],
            ['name' => 'Atty. Rae Abby D. Apasan-Aquino',  'email' => 'raapasanaquino@bsu.edu.ph','position_title' => 'OIC-Dean, College of Criminal Justice Education',    'office' => 'CCJE',   'role' => 'office-head'],
            ['name' => 'Asst. Prof. Renalyn D. Malabanan', 'email' => 'rmalabanan@bsu.edu.ph',   'position_title' => 'Dean, College of Health Sciences',                    'office' => 'CHS',    'role' => 'office-head'],
            ['name' => 'Dr. Anania B. Aquino',              'email' => 'aaquino@bsu.edu.ph',      'position_title' => 'Dean, College of Teacher Education',                  'office' => 'CTE',    'role' => 'office-head'],
            ['name' => 'Asst. Prof. Evangeline S. Barredo','email' => 'ebarredo@bsu.edu.ph',     'position_title' => 'Associate Dean, College of Teacher Education',        'office' => 'CTE',    'role' => 'office-head'],
            ['name' => 'Dr. Jose Alejandro R. Belen',       'email' => 'jabelen@bsu.edu.ph',      'position_title' => 'Principal, Laboratory School',                        'office' => 'LS',     'role' => 'office-head'],
            ['name' => 'Dr. Estelito J. Punongbayan',       'email' => 'epunongbayan@bsu.edu.ph', 'position_title' => 'Head, General Education',                             'office' => 'CICS',   'role' => 'office-head'],
            ['name' => 'Mr. Erwin R. Abiad',                'email' => 'eabiad@bsu.edu.ph',       'position_title' => 'Head, Registration Services',                         'office' => 'RS',     'role' => 'office-head'],
            ['name' => 'Asst. Prof. Analyn H. Venzon',     'email' => 'ahvenzon@bsu.edu.ph',     'position_title' => 'Head, Testing and Admission / Head, Guidance and Counselling', 'office' => 'SDS', 'role' => 'office-head'],
            ['name' => 'Dr. Dyemnah Inasiah H. Bentir',    'email' => 'dibentir@bsu.edu.ph',     'position_title' => 'Head, Student Organization',                          'office' => 'SDS',    'role' => 'office-head'],
            ['name' => 'Asst. Prof. Cherry U. Banta',      'email' => 'cubanta@bsu.edu.ph',      'position_title' => 'Head, Student Discipline',                            'office' => 'SDS',    'role' => 'office-head'],
            ['name' => 'Mrs. Ellaine G. Lid-Ayan',          'email' => 'eglidayan@bsu.edu.ph',    'position_title' => 'Head, Library Services',                              'office' => 'LIB',    'role' => 'office-head'],
            ['name' => 'Asst. Prof. Wilfredo U. Abellera', 'email' => 'wuabellera@bsu.edu.ph',  'position_title' => 'Head, National Service Training Program',             'office' => 'NSTP',   'role' => 'office-head'],
            ['name' => 'Asst. Prof. Arelene M. Mendoza',   'email' => 'amendoza@bsu.edu.ph',     'position_title' => 'Head, Culture and Arts',                              'office' => 'CULT',   'role' => 'office-head'],
            ['name' => 'Asst. Prof. Japner Xavier L. Guevarra', 'email' => 'jxguevarra@bsu.edu.ph', 'position_title' => 'Head, Sports Development Program',               'office' => 'SPORTS', 'role' => 'office-head'],
            ['name' => 'Mr. Albert S. Mercado',             'email' => 'asmercado@bsu.edu.ph',    'position_title' => 'Head, Scholarship and Financial Assistance',          'office' => 'SCHOL',  'role' => 'office-head'],
            ['name' => 'Mr. Rafael D. Sermania',            'email' => 'rdsermania@bsu.edu.ph',   'position_title' => 'Head, Health Services',                               'office' => 'HLTHO',  'role' => 'office-head'],
            ['name' => 'Mr. Marvin E. Rosel',               'email' => 'merosel@bsu.edu.ph',      'position_title' => 'Coordinator, Science Laboratories',                   'office' => 'SCILAB', 'role' => 'office-head'],

            // VC Research staff
            ['name' => 'Dr. Noelyn M. De Jesus',            'email' => 'nmdejesus@bsu.edu.ph',    'position_title' => 'Head, Extension Services',                            'office' => 'EXT',    'role' => 'office-head'],
            ['name' => 'Asst. Prof. Djoanna Marie V. Salac','email' => 'dmvsalac@bsu.edu.ph',    'position_title' => 'Head, Research',                                      'office' => 'RES',    'role' => 'office-head'],

            // VC Administration and Finance staff
            ['name' => 'Ms. Aimee Roxanne U. Percano',     'email' => 'arupercano@bsu.edu.ph',   'position_title' => 'Head, Human Resource Management',                     'office' => 'HRMO',   'role' => 'office-head'],
            ['name' => 'Ar. Lara Patricia E. Cabanillas',  'email' => 'lpecabanillas@bsu.edu.ph','position_title' => 'Head, Project and Facility Management',               'office' => 'PFM',    'role' => 'office-head'],
            ['name' => 'Mrs. Maybelle R. De Las Alas',     'email' => 'mrdelasalas@bsu.edu.ph',  'position_title' => 'Head, Property and Supply',                           'office' => 'PS',     'role' => 'office-head'],
            ['name' => 'Mr. Ebenezer Joshua S. Velasco',   'email' => 'ejsvelasco@bsu.edu.ph',   'position_title' => 'Head, Records Management / Head, Planning and Development', 'office' => 'RMO', 'role' => 'office-head'],
            ['name' => 'Ms. Ana Jean J. Maranan',           'email' => 'ajjmaranan@bsu.edu.ph',   'position_title' => 'Head, General Services',                              'office' => 'GS',     'role' => 'office-head'],
            ['name' => 'Mr. Juvenal R. Oriondo',            'email' => 'jroriondo@bsu.edu.ph',    'position_title' => 'Head, Environmental Management Unit',                 'office' => 'EMU',    'role' => 'office-head'],

            // VC Development and External Affairs staff
            ['name' => 'Mrs. Maria Mariel D. Perea',       'email' => 'mmdperea@bsu.edu.ph',     'position_title' => 'Head, External Affairs',                              'office' => 'EA',     'role' => 'office-head'],
            ['name' => 'Mrs. Monaliza C. Drio',             'email' => 'mcdrio@bsu.edu.ph',       'position_title' => 'Head, Resource Generation',                           'office' => 'RG',     'role' => 'office-head'],
            ['name' => 'Mrs. Leila T. Bayot',               'email' => 'ltbayot@bsu.edu.ph',      'position_title' => 'Head, Alumni and Job Placement Office',               'office' => 'AJPO',   'role' => 'office-head'],

            // Chancellor's office staff
            ['name' => 'Mrs. Marilyn P. Tampes',            'email' => 'mptampes@bsu.edu.ph',     'position_title' => 'Head, Internal Audit Services / Head, Quality Assurance Management', 'office' => 'OC-IAS', 'role' => 'office-head'],

            // Finance / Budget
            ['name' => 'Mrs. Eleonor T. Laña',             'email' => 'etlana@bsu.edu.ph',       'position_title' => 'Head, Budget',                                        'office' => 'BUD',    'role' => 'finance-office'],

            // Accounting
            ['name' => 'Mrs. Apple Kheer R. Chuidian',     'email' => 'akrchuidian@bsu.edu.ph',  'position_title' => 'Head, Accounting',                                    'office' => 'ACCT',   'role' => 'accounting-office'],

            // Procurement
            ['name' => 'Mrs. Marife G. Galvezo',            'email' => 'mggalvezo@bsu.edu.ph',    'position_title' => 'Head, Procurement',                                   'office' => 'PROC',   'role' => 'procurement-office'],

            // Cashier
            ['name' => 'Mrs. Anna Lisa B. Villapando',      'email' => 'alvillapando@bsu.edu.ph', 'position_title' => 'Head, Cashiering',                                    'office' => 'CASH',   'role' => 'cashier'],

            // System admin / ICT
            ['name' => 'Asst. Prof. Renz Mervin A. Salac', 'email' => 'rmasalac@bsu.edu.ph',     'position_title' => 'Head, ICT Services',                                  'office' => 'ICTS',   'role' => 'system-admin'],
        ];

        foreach ($users as $data) {
            $office = $offices[$data['office']];
            $role   = $roles[$data['role']];

            // Generate username from email prefix
            $username = explode('@', $data['email'])[0];

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'username'          => $username,
                    'office_id'         => $office->id,
                    'position_title'    => $data['position_title'],
                    'password'          => Hash::make('prism2025'),
                    'account_status'    => 'active',
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

        // ── VC jurisdiction office assignments ────────────────────────────────────
        // Each VC is assigned a set of offices they supervise.
        // These are non-primary (is_primary = false) and are used to scope
        // their dashboard/reports to only the offices under their authority.

        $vcAssignments = [
            'ljbuenas@bsu.edu.ph'  => ['CICS', 'COE', 'CBA', 'CAS', 'CCJE', 'CHS', 'CTE', 'LS', 'RS', 'SDS', 'LIB', 'NSTP', 'CULT', 'SPORTS', 'SCHOL', 'HLTHO', 'SCILAB'],
            'fdestreza@bsu.edu.ph' => ['RES', 'EXT', 'PD'],
            'jvergara@bsu.edu.ph'  => ['HRMO', 'BUD', 'ACCT', 'CASH', 'PROC', 'PS', 'GS', 'EMU', 'ICTS', 'RMO', 'PFM'],
            'lbalan@bsu.edu.ph'    => ['EA', 'RG', 'AJPO'],
        ];

        $vcRole = $roles['vice-chancellor'];

        foreach ($vcAssignments as $email => $officeCodes) {
            $vc = User::where('email', $email)->first();
            if (!$vc) continue;

            $assignmentMap = collect($officeCodes)->mapWithKeys(function (string $code) use ($offices, $vcRole) {
                return [
                    $offices[$code]->id => [
                        'role_in_office' => $vcRole->name,
                        'starts_on'      => now()->toDateString(),
                        'is_primary'     => false,
                    ],
                ];
            })->all();

            $vc->officeAssignments()->syncWithoutDetaching($assignmentMap);
        }
    }
}

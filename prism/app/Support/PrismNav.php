<?php

namespace App\Support;

class PrismNav
{
    /** Cross-role navigation shown in the shared layout's role switcher. */
    public static function roleNavigation(): array
    {
        return [
            ['slug' => 'office-head',        'label' => 'Office Head / Dean',  'href' => route('office-head.dashboard')],
            ['slug' => 'finance-office',     'label' => 'Finance Office',       'href' => route('finance-office.dashboard')],
            ['slug' => 'procurement-office', 'label' => 'Procurement Office',   'href' => route('procurement-office.dashboard')],
            ['slug' => 'bac',                'label' => 'BAC',                  'href' => route('bac.dashboard')],
            ['slug' => 'chancellor',         'label' => 'Chancellor',           'href' => route('chancellor.dashboard')],
            ['slug' => 'vice-chancellor',    'label' => 'Vice Chancellor',      'href' => route('vice-chancellor.dashboard')],
            ['slug' => 'accounting-office',  'label' => 'Accounting Office',    'href' => route('accounting-office.dashboard')],
            ['slug' => 'cashier',            'label' => 'Cashier',              'href' => route('cashier.dashboard')],
            ['slug' => 'admin',              'label' => 'Admin',                'href' => route('admin.dashboard')],
        ];
    }
}

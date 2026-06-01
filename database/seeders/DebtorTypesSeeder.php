<?php

namespace Database\Seeders;

use App\Models\System\DebtorType;
use Illuminate\Database\Seeder;

class DebtorTypesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Cash', 'slug' => 'cash', 'description' => 'Cash / prepaid debtor terms.'],
            ['name' => 'Credit', 'slug' => 'credit', 'description' => 'Credit / postpaid debtor terms.'],
        ] as $row) {
            DebtorType::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name'], 'description' => $row['description'], 'is_active' => true]
            );
        }
    }
}

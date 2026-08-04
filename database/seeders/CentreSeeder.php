<?php

namespace Database\Seeders;

use App\Models\Centre;
use Illuminate\Database\Seeder;

class CentreSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Centre::centresPredefinis() as $data) {
            Centre::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}

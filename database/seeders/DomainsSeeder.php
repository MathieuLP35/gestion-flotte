<?php

namespace Database\Seeders;

use App\Models\AllowedDomain;
use Illuminate\Database\Seeder;

class DomainsSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            'gmail.com',
        ];

        foreach ($domains as $domain) {
            AllowedDomain::firstOrCreate(['name' => $domain]);
        }
    }
}

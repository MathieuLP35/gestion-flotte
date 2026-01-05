<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AllowedDomain;

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
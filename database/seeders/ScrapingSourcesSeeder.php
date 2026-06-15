<?php

namespace Database\Seeders;

use App\Models\ScrapingSource;
use Illuminate\Database\Seeder;

class ScrapingSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScrapingSource::updateOrCreate(
            ['base_url' => 'https://oatd.org'],
            [
                'name' => 'OATD (Open Access Theses and Dissertations)',
                'is_active' => true,
            ]
        );
    }
}

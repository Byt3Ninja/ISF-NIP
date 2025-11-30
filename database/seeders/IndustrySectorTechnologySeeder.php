<?php

namespace Database\Seeders;

use App\Sector;
use App\StaticCategory;
use App\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IndustrySectorTechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(base_path('database/dbFiles/industries_sectors_technologies.json'));
        $data = json_decode($json, true);

        foreach ($data as $item) {
            // Create or update Industry (StaticCategory)
            $industry = StaticCategory::updateOrCreate(
                ['name_en' => $item['industry']['en']],
                ['name_ar' => $item['industry']['ar']]
            );

            // Process Sectors
            if (isset($item['sectors']) && is_array($item['sectors'])) {
                foreach ($item['sectors'] as $sectorData) {
                    $sector = Sector::updateOrCreate(
                        ['name_en' => $sectorData['en']],
                        ['name_ar' => $sectorData['ar']]
                    );

                    // Attach Sector to Industry if not already attached
                    if (!$industry->sectors()->where('sector_id', $sector->id)->exists()) {
                        $industry->sectors()->attach($sector->id);
                    }
                }
            }

            // Process Technologies
            if (isset($item['technologies']) && is_array($item['technologies'])) {
                foreach ($item['technologies'] as $techName) {
                    Technology::firstOrCreate(
                        ['name' => $techName],
                        ['type' => 'General', 'is_active' => true]
                    );
                }
            }
        }
    }
}

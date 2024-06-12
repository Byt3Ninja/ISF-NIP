<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         $this->call(AdminSeeder::class);
//         $this->call(SettingSeeder::class);
//         $this->call(CountryStateCitySeeder::class);
//         $this->call(FieldSeeder::class);
//         $this->call(SectorSeeder::class);
//         $this->call(RolePermissionSeeder::class);
//         $this->call(DocumentationSeeder::class);
//         $this->call(ServiceCategorySeeder::class);
        $this->call(ExpertiseSeeder::class);
    }
}

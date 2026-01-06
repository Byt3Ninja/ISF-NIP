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
        $this->call(defaultRolesPermissionSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(CountryStateCitySeeder::class);
        $this->call(FieldSeeder::class);
        $this->call(SectorSeeder::class);
        // $this->call(RolePermissionSeeder::class);
        $this->call(DocumentationSeeder::class);
        $this->call(ServiceCategorySeeder::class);
        $this->call(ExpertiseSeeder::class);
        $this->call(StagesTableSeeder::class);
        $this->call(QuestionsBankSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(MigrateOrgIdSeeder::class);
        $this->call(SustainableIconSeeder::class);
        $this->call(WeightsTableSeeder::class);
        // $this->call(CycleSeeder::class);
        // $this->call(CompanySeeder::class);
        // $this->call(SlotSeeder::class);
    }
}

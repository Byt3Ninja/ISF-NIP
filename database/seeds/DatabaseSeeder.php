<?php

use Illuminate\Database\Seeder;
use database\Seeders\SettingSeeder;
use database\seeds\AdminSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(AdminSeeder::class);
//         $this->call(SettingSeeder::class);
    }
}

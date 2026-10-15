<?php

namespace Database\Seeders;

use App\Cycle;
use App\Organization;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first organization or create one if none exists
        $organization = Organization::first();
        if (!$organization) {
            $organization = Organization::create([
                'name_en' => 'Test Organization',
                'name_ar' => 'منظمة تجريبية',
                'description_en' => 'Test Organization for Development',
                'description_ar' => 'منظمة تجريبية للتطوير',
                'logo' => null,
                'website' => 'https://test.org',
                'email' => 'info@test.org',
                'phone' => '+1234567890',
                'address_en' => 'Test Address',
                'address_ar' => 'عنوان تجريبي',
                'country_id' => 1, // Assuming Egypt or first country
                'state_id' => 1,
                'city_id' => 1,
            ]);
        }

        // Create fake programs/cycles
        $cycles = [
            [
                'title' => 'Innovation Accelerator Program 2024',
                'description' => 'A comprehensive program to accelerate innovative startups in the tech sector.',
                'organization_id' => $organization->id,
                'start' => Carbon::now()->subDays(30),
                'end' => Carbon::now()->addDays(150),
                'type' => 'General_Program',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Digital Transformation Challenge',
                'description' => 'Challenge focused on digital transformation solutions for traditional businesses.',
                'organization_id' => $organization->id,
                'start' => Carbon::now()->addDays(10),
                'end' => Carbon::now()->addDays(100),
                'type' => 'Hackathon',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Sustainable Tech Initiative',
                'description' => 'Supporting startups working on sustainable technology solutions.',
                'organization_id' => $organization->id,
                'start' => Carbon::now()->subDays(60),
                'end' => Carbon::now()->addDays(90),
                'type' => 'General_Program',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Empty Program (No Startups)',
                'description' => 'This program has no subscribed startups for testing purposes.',
                'organization_id' => $organization->id,
                'start' => Carbon::now()->addDays(20),
                'end' => Carbon::now()->addDays(120),
                'type' => 'General_Program',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($cycles as $cycleData) {
            // organization_id is not mass-assignable; this is trusted seed data.
            Cycle::forceCreate($cycleData);
        }

        $this->command->info('Fake cycles/programs created successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed 40 slots for user ID 128 (20 Period + 20 Weekly)
        $userId = 128;
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = [
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['13:00:00', '14:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00'],
            ['16:00:00', '17:00:00'],
        ];
        $sessionTypes = ['Online', 'Offline', 'Both'];

        // Generate dates for the next 20 days
        $dates = [];
        for ($i = 0; $i < 20; $i++) {
            $dates[] = Carbon::now()->addDays($i)->toDateString();
        }

        $slots = [];
        $slotCount = 0;
        $sessionTypeIndex = 0;

        // Create 20 Period slots with date field
        for ($i = 0; $i < 20; $i++) {
            $date = $dates[$i % count($dates)];
            $timeSlot = $timeSlots[$i % count($timeSlots)];
            $sessionType = $sessionTypes[$sessionTypeIndex];

            // Change session type every 5 slots
            if (($i + 1) % 5 === 0) {
                $sessionTypeIndex = ($sessionTypeIndex + 1) % count($sessionTypes);
            }

            $slots[] = [
                'user_id' => $userId,
                'type' => 'Period',
                'days' => null,
                'date' => $date,
                'tfrom' => $timeSlot[0],
                'tto' => $timeSlot[1],
                'session_type' => $sessionType,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $slotCount++;
        }

        // Create 20 Weekly slots with days field
        for ($i = 0; $i < 20; $i++) {
            $day = $daysOfWeek[$i % count($daysOfWeek)];
            $timeSlot = $timeSlots[$i % count($timeSlots)];
            $sessionType = $sessionTypes[$sessionTypeIndex];

            // Change session type every 5 slots
            if (($i + 1) % 5 === 0) {
                $sessionTypeIndex = ($sessionTypeIndex + 1) % count($sessionTypes);
            }

            $slots[] = [
                'user_id' => $userId,
                'type' => 'Weekly',
                'days' => $day,
                'date' => null,
                'tfrom' => $timeSlot[0],
                'tto' => $timeSlot[1],
                'session_type' => $sessionType,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $slotCount++;
        }

        DB::table('schedules')->insert($slots);
    }
}

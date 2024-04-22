<?php

namespace database\seeds;

use App\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seed;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name_en'=>'Super',
            'last_name_en'=>'Admin',
            'first_name_ar'=>'سوبر',
            'last_name_ar'=>'أدمن',
            'email'=>'admin@growthlabs.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);
    }
}

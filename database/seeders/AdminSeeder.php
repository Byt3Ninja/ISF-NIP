<?php

namespace Database\Seeders;

use App\User;
use App\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@growthlabs.app'],
            [
            'first_name_en'=>'Super',
            'last_name_en'=>'Admin',
            'first_name_ar'=>'سوبر',
            'last_name_ar'=>'أدمن',
            'email'=>'admin@growthlabs.app',
            'password' => '$2y$10$jC2iDPRC5y6IpkNvHj3dZ.P3kh5INpWd01Pyz0pc0Kdatjmic3oq2', // password
            'email_verified_at'=>now()
        ]);
        $user = User::first();
        $getRole = Role::where('default_for', 'LIKE', '%admin%')->first();
        if( empty( $getRole ) )
        {
            $getRole = new Role;
            $getRole->name = 'Admin';
            $getRole->default_for = 'admin';
            $getRole->guard_name = 'web';
            $getRole->save();
        }
        $user->allotRole('admin');
    }
}

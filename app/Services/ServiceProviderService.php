<?php

namespace App\Services;

use App\User;
use App\serviceProviders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceProviderService
{
    public function updateStep1(User $user, array $data, $file = null)
    {
        return DB::transaction(function () use ($user, $data, $file) {
            
            // Handle Image Upload
            if ($file) {
                $data['img_url'] = $file->store('service_providers_logos', ['disk' => 'public_uploads']);
            }

            // Map frontend keys to DB keys if necessary
            $dbData = [
                'company_name' => $data['company_name'],
                'contact_name' => $data['contact_name'],
                'email' => $data['email'],
                'phone' => $data['phone'], // Assuming partial phone, will concat below if needed or just use as is
                // 'country_code' is not directly in table based on migration? Check migration again.
                // Migration didn't add country_code, it was already there?
                // serviceProviders model had 'phone' but let's check migration again or model.
                // Model has 'country_code' in casts but not fillable? Correct it if needed.
                // Actually model has 'country_code' in casts line 32, but not fillable line 20.
                // Migration just added new fields. Original table might have had country_code.
                
                'whatsapp' => $data['whatsapp'] ?? null,
                'country_code_w_a' => $data['country_code_w_a'] ?? null,
                'preferred_language' => $data['language'] ?? null,
                
                'country_id' => $data['country'],
                'state_id' => $data['state'],
                'city_id' => $data['city'],
                
                'address' => $data['address'] ?? null,
                'zip_code' => $data['zip'] ?? null,
                'description' => $data['about'],
            ];

            if(isset($data['img_url'])) {
                $dbData['img_url'] = $data['img_url'];
            }

            // Update Service Provider Profile
            $provider = $user->service_provider;
            
            if (!$provider) {
                // Should exist if role is serviceProvider, but create if not?
                $provider = new serviceProviders();
                $provider->user_id = $user->id;
                $provider->status = true; // Default or depend on registration
            }

            // We might need to handle country_code if it's separate
            if(isset($data['country_code'])) {
                 // The 'phone' field in DB might expect full number or just local. 
                 // The frontend passes 'country_code' and 'phone'.
                 // Let's look at registration controller `registration_request`
                 // $registration->phone = '+' . $request->country_code . $request->phone;
                 // It saves concatenated.
                 // But validation rule `phone` says 'string'.
                 
                 // If the user sends broken down, we might want to save concatenated.
                 // For now let's assume we save what we get or concatenated.
                 // Let's check `_service_provider_basics.blade.php`:
                 // <input name="phone"> and <input name="country_code">.
                 
                 // So we should concat.
                 $dbData['phone'] = '+' . $data['country_code'] . $data['phone'];
            } else {
                 $dbData['phone'] = $data['phone'];
            }
            
            $provider->fill($dbData);
            $provider->save();
            
            // Also update User table? usually keeping them in sync is good
            $user->email = $data['email'];
            $user->first_name_en = explode(' ', $data['contact_name'])[0];
            // etc... leaving user update minimal for now to avoid side effects
            $user->save();

            return $provider;
        });
    }

    public function updateStep2(User $user, array $data, $file = null)
    {
        return DB::transaction(function () use ($user, $data, $file) {
            
            // Handle Hero Image Upload
            if ($file) {
                $data['hero_image'] = $file->store('service_providers_hero', ['disk' => 'public_uploads']);
            }

            $dbData = [
                'hero_description' => $data['hero_description'],
                'services_short_description' => $data['services_short_description'],
                'appointment_link' => $data['appointment_link'] ?? null,
                'operating_mode' => $data['operating_mode'],
                'operating_region' => $data['operating_region'],
            ];

            if(isset($data['hero_image'])) {
                $dbData['hero_image'] = $data['hero_image'];
            }

            $provider = $user->service_provider;
            if (!$provider) {
                $provider = new serviceProviders();
                $provider->user_id = $user->id;
                $provider->status = true;
            }

            $provider->fill($dbData);
            $provider->save();

            return $provider;
        });
    }

    public function updateStep3(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            
            $dbData = [
                'show_stats' => $data['stats_visibility'] === 'on',
            ];

            for ($i = 1; $i <= 4; $i++) {
                $dbData["stat{$i}_label"] = $data["label{$i}"] ?? null;
                $dbData["stat{$i}_value"] = $data["value{$i}"] ?? null;
            }

            $provider = $user->service_provider;
            if (!$provider) {
                $provider = new serviceProviders();
                $provider->user_id = $user->id;
                $provider->status = true;
            }

            $provider->fill($dbData);
            $provider->save();

            return $provider;
        });
    }
}

<?php

namespace App\Services;

use App\User;
use App\serviceProviders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FileManagerService;

class ServiceProviderService
{
    public function updateStep1(User $user, array $data, $file = null, serviceProviders $provider = null)
    {
        return DB::transaction(function () use ($user, $data, $file, $provider) {

            // Handle Image Upload
            if ($file) {
                $data['img_url'] = app(FileManagerService::class)->upload($file, 'service_providers_logos', 'public_uploads', 'image');
            }

            $dbData = [
                'company_name' => $data['company_name'],
                'contact_name' => $data['contact_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
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

            // Use explicit provider if given, otherwise fall back to user's provider
            if (!$provider) {
                $provider = $user->service_provider;
            }

            if (!$provider) {
                $provider = new serviceProviders();
                $provider->user_id = $user->id;
                $provider->status = true;
            }

            if(isset($data['country_code'])) {
                 $dbData['phone'] = '+' . $data['country_code'] . $data['phone'];
            } else {
                 $dbData['phone'] = $data['phone'];
            }

            $provider->fill($dbData);
            $provider->save();

            // Sync email/name to the provider's owner user
            $targetUser = $provider->user;
            if ($targetUser) {
                $targetUser->email = $data['email'];
                $targetUser->first_name_en = explode(' ', $data['contact_name'])[0];
                $targetUser->save();
            }

            return $provider;
        });
    }

    public function updateStep2(User $user, array $data, $file = null, serviceProviders $provider = null)
    {
        return DB::transaction(function () use ($user, $data, $file, $provider) {

            if ($file) {
                $data['hero_image'] = app(FileManagerService::class)->upload($file, 'service_providers_hero', 'public_uploads', 'image');
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

            if (!$provider) {
                $provider = $user->service_provider;
            }
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

    public function updateStep3(User $user, array $data, serviceProviders $provider = null)
    {
        return DB::transaction(function () use ($user, $data, $provider) {

            $dbData = [
                'show_stats' => $data['stats_visibility'] === 'on',
            ];

            for ($i = 1; $i <= 4; $i++) {
                $dbData["stat{$i}_label"] = $data["label{$i}"] ?? null;
                $dbData["stat{$i}_value"] = $data["value{$i}"] ?? null;
            }

            if (!$provider) {
                $provider = $user->service_provider;
            }
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

    public function updateStep4(User $user, array $data, serviceProviders $provider = null)
    {
        return DB::transaction(function () use ($user, $data, $provider) {
            if (!$provider) {
                $provider = $user->service_provider;
            }
            if (!$provider) {
                $provider = new serviceProviders();
                $provider->user_id = $user->id;
                $provider->status = true;
                $provider->save();
            }

            $provider->update([
                'show_portfolio' => $data['portfolio_visibility'] === 'on',
                'portfolio_description' => $data['portfolio_description'] ?? null,
            ]);

            $portfolioData = $data['portfolio'] ?? [];
            $existingIds = [];

            foreach ($portfolioData as $index => $item) {
                if (isset($item['delete']) && $item['delete'] === 'true') {
                    if (isset($item['id'])) {
                        $provider->portfolios()->where('id', $item['id'])->delete();
                    }
                    continue;
                }

                // Prepare item data
                $itemData = [
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'order' => $index,
                ];

                // Handle Media Upload
                if (isset($item['media']) && $item['media'] instanceof \Illuminate\Http\UploadedFile) {
                    $storedPath = app(FileManagerService::class)->upload($item['media'], 'service_providers_portfolio', 'public', 'media');
                    if ($storedPath) {
                        $itemData['media_url'] = $storedPath;
                        $itemData['media_type'] = str_starts_with($item['media']->getMimeType(), 'video') ? 'video' : 'image';
                    } else {
                        Log::error('Portfolio media upload failed', [
                            'provider_id' => $provider->id,
                            'original_name' => $item['media']->getClientOriginalName(),
                        ]);
                    }
                }

                if (!empty($item['id'])) {
                    $portfolio = $provider->portfolios()->find($item['id']);
                    if ($portfolio) {
                        $portfolio->update($itemData);
                        $existingIds[] = $portfolio->id;
                    }
                } else {
                    // Only create if at least one field is provided (though validation should handle this)
                    if ($itemData['title'] || $itemData['description'] || isset($itemData['media_url'])) {
                        $portfolio = $provider->portfolios()->create($itemData);
                        $existingIds[] = $portfolio->id;
                    }
                }
            }

            return $provider->load('portfolios');
        });
    }
}

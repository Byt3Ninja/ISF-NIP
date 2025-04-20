<?php

namespace App\Http\Controllers;

use App\License;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class LicenseController extends Controller
{
    public function showActivateForm()
    {
        $activeLicense = License::where('status', 'ACTIVE')
        ->where('expires_at', '>', now())
        ->first();

        if( empty( $activeLicense ) )
        {
            return view('admin.license.activate');
        }
        try {
            $payload = [
                'meta' => [
                    'key' => $activeLicense->key,
                ]
            ];
    
            $response = Http::withToken(config('services.keygen.token'))
                ->post('https://api.keygen.sh/v1/accounts/' . config('services.keygen.account') . '/licenses/actions/validate-key', $payload);
    
            // Check if the request failed
            if ($response->failed()) {
                $errorDetails = $response->json('errors');
                $errorMessage = $errorDetails ? implode(' ', array_column($errorDetails, 'detail')) : 'Unknown error occurred.';
                return $this->errorResult('License Failed: ' . $errorMessage);
            }
    
            $valid = $response->json('meta.valid');
            $licenseData = $response->json('data');
    
           if ($valid) {
    
            return view('admin.license.result', [
                'message' => __('admin.licenseActivated'),
                'success' => true,
                'license' => $licenseData,
                'status' => $licenseData['attributes']['status'],
                'expiresAt' => Carbon::parse($licenseData['attributes']['expiry']),
                'maxMachines' => $licenseData['attributes']['maxMachines'],
                'createdAt' => Carbon::parse($licenseData['attributes']['created']),
            ]);
        }
            $statusMessage = $this->getLicenseStatusMessage($licenseData['attributes']['status']);
            return $this->errorResult('License is not valid or has expired. ' . $statusMessage);
    
        } catch (\Exception $e) {
            return $this->errorResult('License failed. Please try again. Error: ' . $e->getMessage());
        }

    if ($activeLicense) {
        return view('admin.license.result', [
            'message' => __('admin.licenseAlreadyActivated') . ' ' . $activeLicense->expires_at->translatedFormat('Y-m-d H:i'),
            'success' => true,
            'license' => $activeLicense
        ]);

    }

  
        return view('admin.license.activate' );
    }

    public function activate(Request $request)
    {
        $licenseKey = $request->license_key;
    
        try {
            $payload = [
                'meta' => [
                    'key' => $licenseKey,
                ]
            ];
    
            $response = Http::withToken(config('services.keygen.token'))
                ->post('https://api.keygen.sh/v1/accounts/' . config('services.keygen.account') . '/licenses/actions/validate-key', $payload);
    
            // Check if the request failed
            if ($response->failed()) {
                $errorDetails = $response->json('errors');
                $errorMessage = $errorDetails ? implode(' ', array_column($errorDetails, 'detail')) : 'Unknown error occurred.';
                return $this->errorResult('License Failed: ' . $errorMessage);
            }
    
            $valid = $response->json('meta.valid');
            $licenseData = $response->json('data');

        if ($valid) {
            License::updateOrCreate(
                ['key' => $licenseData['attributes']['key']],
                [
                    'status' => $licenseData['attributes']['status'],
                    'activated_at' => now(),
                    'expires_at' => Carbon::parse($licenseData['attributes']['expiry']),
                ]
            );

           
            return view('admin.license.result', [
                'message' => __('admin.licenseActivated'),
                'success' => true,
                'license' => $licenseData,
                'status' => $licenseData['attributes']['status'],
                'expiresAt' => Carbon::parse($licenseData['attributes']['expiry']),
                'maxMachines' => $licenseData['attributes']['maxMachines'],
                'createdAt' => Carbon::parse($licenseData['attributes']['created']),
            ]);
        }
            $statusMessage = $this->getLicenseStatusMessage($licenseData['attributes']['status']);
            return $this->errorResult('License is not valid or has expired. ' . $statusMessage);
    
        } catch (\Exception $e) {
            return $this->errorResult('License failed. Please try again. Error: ' . $e->getMessage());
        }
    }
    
protected function getLicenseStatusMessage($status)
{
        $statusMessages = [
            'EXPIRED' => 'License expired. Please renew your license.',
            'SUSPENDED' => 'License suspended. Contact support for assistance.',
            'REVOKED' => 'License revoked. This license is no longer valid.',
            'INVALID' => 'Invalid license key.',
            'INACTIVE' => 'License is inactive. Activation required.',
        ];
    
        return $statusMessages[$status] ?? 'Unknown license status returned by API. Please contact support for assistance.';
}

private function errorResult($message)
{
        return view('admin.license.result', ['message' => $message, 'success' => false]);
}

   
}

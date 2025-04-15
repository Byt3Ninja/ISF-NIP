<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class LicenseController extends Controller
{
    public function showActivateForm()
    {
        return view('admin.license.activate');
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
            $licenseStatus = $response->json('meta.status');
    
            if ($valid) {
                return view('admin.license.result', ['message' => __('admin.licenseActivated'), 'success' => true]);
            }
    
            $statusMessage = $this->getLicenseStatusMessage($licenseStatus);
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

<?php

namespace App\Http\Requests;

use App\serviceProviders;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceProviderStep1Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // When editing an existing provider, exclude its owner from the email uniqueness check
        $ignoreUserId = null;
        if ($this->filled('service_provider_id')) {
            $provider = serviceProviders::find($this->service_provider_id);
            $ignoreUserId = $provider?->user_id;
        } else {
            $ignoreUserId = auth()->id();
        }

        $emailRule = 'required|email:rfc,dns|max:50';
        if ($ignoreUserId) {
            $emailRule .= '|unique:users,email,' . $ignoreUserId;
        }

        return [
            'company_name' => 'required|string|max:50',
            'contact_name' => 'required|string|max:50',
            'email' => $emailRule,
            'phone' => 'required|string|max:30|regex:/^[\d\s\-\(\)]+$/',
            'country_code' => 'required|string',

            'whatsapp' => 'nullable|string|max:30|regex:/^[\d\s\-\(\)]+$/',
            'country_code_w_a' => 'nullable|string',
            'language' => 'nullable|string', // Frontend name is 'language' but DB is 'preferred_language'

            'country' => 'required|exists:countries,id',
            'state' => 'required|exists:states,id',
            'city' => 'nullable|exists:cities,id',
            
            'address' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:9',
            'about' => 'required|string|max:500',
            
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'service_provider_id' => 'nullable|integer|exists:service_providers,id',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\MentorshipTextLimit;
use App\Services\TimezoneService;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreMentorshipSessionRequest extends FormRequest
{
    use ApiResponder;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'mentor_id' => 'required|exists:users,id',
            // GV-9566: `after:now` reads an offset-less value in the storage zone; the
            // time is platform wall-clock of the organization (an explicit offset wins).
            'preferred_date_time' => ['required', 'date', function (string $attribute, $value, Closure $fail) {
                try {
                    $zone = TimezoneService::platform($this->input('organization_id') ? (int) $this->input('organization_id') : null);
                    $at = Carbon::parse($value, $zone);
                } catch (\Exception $e) {
                    return; // the `date` rule reports it
                }
                if (!$at->isFuture()) {
                    $fail(__('validation.after', ['attribute' => $attribute, 'date' => 'now']));
                }
            }],
            'duration_minutes' => 'required|integer|in:30,45,60',
            'override_availability' => 'boolean',
            'override_reason' => 'required_if:override_availability,true|string|max:100',
            'notes' => 'nullable|string|max:' . MentorshipTextLimit::NOTES->value,
            'cycle_id' => 'required|exists:cycles,id', 
            'organization_id' => 'required|exists:organizations,id', 
        ];
    }

    public function messages(): array
    {
        return [
            'notes.max' => __('mentor.notes_max_length', ['max' => MentorshipTextLimit::NOTES->value]),
        ];
    }

     protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(__('api.validation_error'), 422, $validator->errors())
        );
    }
}

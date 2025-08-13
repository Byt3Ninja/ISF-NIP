<?php

namespace App\Http\Requests;

use App\Traits\ApiResponder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateBioRequest extends FormRequest
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
            'bio'                              => 'nullable|string|max:100',
            'specialization'                   => 'nullable|string|max:250',
            'linkedin'                         => 'nullable|url|max:250',
            'entrepreneurialExperience'        => 'nullable|boolean',
            'entrepreneurialExperienceBrief'   => 'nullable|string',
            'profilePrivacy'                    => 'nullable|boolean',
            'expertiseIds'                      => 'nullable|array',
            'expertiseIds.*'                    => 'exists:expertises,id',
            'mentorshipApproach'                => 'nullable|array',
            'mentorshipApproach.*'              => 'string|in:E-mail,Phone Calls,Video Calls,In-Person Meetings',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->validationError(
                __('api.validation_error'),
                422,
                $validator->errors()
            )
        );
    }
}

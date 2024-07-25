<?php

namespace App\Http\Requests\Mentor\MentorshipTask;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
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
            'mentorship_request_id'=>'sometimes|exists:mentorship_requests,id',
            'title'=>'required|string',
            'description'=>'required|string',
        ];
    }
}

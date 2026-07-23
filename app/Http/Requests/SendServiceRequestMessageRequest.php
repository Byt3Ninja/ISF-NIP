<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendServiceRequestMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id'    => 'required|integer|exists:services_requests,id',
            'message'       => 'required|string|min:1|max:1000',
            'attachment'    => 'nullable|file|max:10240|mimes:png,jpg,jpeg,webp,doc,docx,xls,xlsx,ppt,pptx,zip,rar',
            'attachments'   => 'nullable',
            'attachments.*' => 'nullable|file|max:10240|mimes:png,jpg,jpeg,webp,doc,docx,xls,xlsx,ppt,pptx,zip,rar',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required'      => __('admin.message_required'),
            'message.max'           => __('admin.message_body_length'),
            'message.min'           => __('admin.message_body_min'),
            'attachment.max'        => __('admin.file_size_exceeds_limit'),
            'attachment.mimes'      => __('admin.unsupported_file_type'),
            'attachments.*.max'     => __('admin.file_size_exceeds_limit'),
            'attachments.*.mimes'   => __('admin.unsupported_file_type'),
        ];
    }
}

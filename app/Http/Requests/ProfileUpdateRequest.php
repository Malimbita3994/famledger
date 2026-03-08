<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => [
                'nullable',
                'image', // must be a real image (validates content, not just extension)
                'mimes:jpeg,png,jpg,gif',
                'mimetypes:image/jpeg,image/png,image/gif', // MIME from file content, blocks scripts
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.image' => __('The avatar must be a valid image file (JPEG, PNG or GIF).'),
            'avatar.mimetypes' => __('The avatar must be a real image file. Scripts and other file types are not allowed.'),
        ];
    }
}

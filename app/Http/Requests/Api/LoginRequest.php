<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'pn' => 'required|string|max:8',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pn.required' => 'Personal Number is required.',
            'pn.max' => 'Personal Number must not exceed 8 characters.',
            'password.required' => 'Password is required.',
            'device_name.required' => 'Device name is required.',
            'device_name.max' => 'Device name must not exceed 255 characters.'
        ];
    }
}

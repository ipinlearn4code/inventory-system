<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReportIssueRequest extends FormRequest
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
            'description' => 'required|string|max:1000',
            'date' => 'required|date_format:Y-m-d'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Problem description is required.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'date.required' => 'Issue date is required.',
            'date.date_format' => 'Date must be in YYYY-MM-DD format.'
        ];
    }
}

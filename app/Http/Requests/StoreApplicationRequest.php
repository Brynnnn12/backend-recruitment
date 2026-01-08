<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming authenticated users can apply
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vacancy_id' => 'required|exists:vacancies,id',
            'cv_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'vacancy_id.required' => 'Vacancy ID is required.',
            'vacancy_id.exists' => 'The selected vacancy does not exist.',
            'cv_file.required' => 'CV file is required.',
            'cv_file.file' => 'CV must be a file.',
            'cv_file.mimes' => 'CV must be a PDF file.',
            'cv_file.max' => 'CV file size must not exceed 5MB.',
        ];
    }
}

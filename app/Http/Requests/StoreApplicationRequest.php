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
            'vacancy_id' => [
                'required',
                'integer',
                'exists:vacancies,id',
                'min:1'
            ],
            'cv_file' => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:5120', // 5MB
                'extensions:pdf'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vacancy_id.required' => 'Lowongan pekerjaan harus diisi.',
            'vacancy_id.integer' => 'ID lowongan harus berupa angka.',
            'vacancy_id.exists' => 'Lowongan yang dipilih tidak ditemukan.',
            'vacancy_id.min' => 'ID lowongan tidak valid.',
            'cv_file.required' => 'File CV harus diunggah.',
            'cv_file.file' => 'CV harus berupa file yang valid.',
            'cv_file.mimes' => 'CV harus berupa file PDF.',
            'cv_file.mimetypes' => 'Tipe MIME file harus application/pdf.',
            'cv_file.max' => 'Ukuran file CV maksimal 5MB.',
            'cv_file.extensions' => 'Ekstensi file harus .pdf',
        ];
    }
}

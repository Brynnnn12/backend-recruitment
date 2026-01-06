<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVacancyRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'string'],
            'location' => ['sometimes', 'string', 'max:100'],
            'type' => ['sometimes', 'in:full-time,part-time,contract,internship'],
            'status' => ['sometimes', 'in:open,closed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => 'Judul lowongan kerja harus berupa teks.',
            'title.max' => 'Judul lowongan kerja maksimal 50 karakter.',
            'description.string' => 'Deskripsi lowongan kerja harus berupa teks.',
            'location.string' => 'Lokasi lowongan kerja harus berupa teks.',
            'location.max' => 'Lokasi lowongan kerja maksimal 100 karakter.',
            'type.in' => 'Tipe lowongan kerja harus salah satu dari: full-time, part-time, contract, internship.',
            'status.in' => 'Status lowongan kerja harus salah satu dari: open, closed.',
        ];
    }
}

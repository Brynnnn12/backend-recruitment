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
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-\.]+$/u'
            ],
            'description' => [
                'sometimes',
                'string',
                'min:10',
                'max:5000'
            ],
            'location' => [
                'sometimes',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\,\.]+$/u'
            ],
            'type' => [
                'sometimes',
                'string',
                'in:full-time,part-time,contract,internship'
            ],
            'status' => [
                'sometimes',
                'string',
                'in:open,closed'
            ],
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
            'title.min' => 'Judul lowongan kerja minimal 3 karakter.',
            'title.max' => 'Judul lowongan kerja maksimal 50 karakter.',
            'title.regex' => 'Judul lowongan kerja hanya boleh mengandung huruf, angka, spasi, tanda hubung, dan titik.',
            'description.string' => 'Deskripsi lowongan kerja harus berupa teks.',
            'description.min' => 'Deskripsi lowongan kerja minimal 10 karakter.',
            'description.max' => 'Deskripsi lowongan kerja maksimal 5000 karakter.',
            'location.string' => 'Lokasi lowongan kerja harus berupa teks.',
            'location.min' => 'Lokasi lowongan kerja minimal 3 karakter.',
            'location.max' => 'Lokasi lowongan kerja maksimal 100 karakter.',
            'location.regex' => 'Lokasi lowongan kerja hanya boleh mengandung huruf, angka, spasi, koma, tanda hubung, dan titik.',
            'type.string' => 'Tipe lowongan kerja harus berupa teks.',
            'type.in' => 'Tipe lowongan kerja harus salah satu dari: full-time, part-time, contract, internship.',
            'status.string' => 'Status lowongan kerja harus berupa teks.',
            'status.in' => 'Status lowongan kerja harus salah satu dari: open, closed.',
        ];
    }
}

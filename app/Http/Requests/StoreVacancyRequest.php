<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
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
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-\.]+$/u'
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000'
            ],
            'location' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\,\.]+$/u'
            ],
            'type' => [
                'required',
                'string',
                'in:full-time,part-time,contract,internship'
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
            'title.required' => 'Judul lowongan kerja wajib diisi.',
            'title.string' => 'Judul lowongan kerja harus berupa teks.',
            'title.min' => 'Judul lowongan kerja minimal 3 karakter.',
            'title.max' => 'Judul lowongan kerja maksimal 50 karakter.',
            'title.regex' => 'Judul lowongan kerja hanya boleh mengandung huruf, angka, spasi, tanda hubung, dan titik.',
            'description.required' => 'Deskripsi lowongan kerja wajib diisi.',
            'description.string' => 'Deskripsi lowongan kerja harus berupa teks.',
            'description.min' => 'Deskripsi lowongan kerja minimal 10 karakter.',
            'description.max' => 'Deskripsi lowongan kerja maksimal 5000 karakter.',
            'location.required' => 'Lokasi lowongan kerja wajib diisi.',
            'location.string' => 'Lokasi lowongan kerja harus berupa teks.',
            'location.min' => 'Lokasi lowongan kerja minimal 3 karakter.',
            'location.max' => 'Lokasi lowongan kerja maksimal 100 karakter.',
            'location.regex' => 'Lokasi lowongan kerja hanya boleh mengandung huruf, angka, spasi, koma, tanda hubung, dan titik.',
            'type.required' => 'Tipe lowongan kerja wajib diisi.',
            'type.string' => 'Tipe lowongan kerja harus berupa teks.',
            'type.in' => 'Tipe lowongan kerja harus salah satu dari: full-time, part-time, contract, internship.',
        ];
    }
}

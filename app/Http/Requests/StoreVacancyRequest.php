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
            'title' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:full-time,part-time,contract,internship'],
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
            'title.max' => 'Judul lowongan kerja maksimal 50 karakter.',
            'description.required' => 'Deskripsi lowongan kerja wajib diisi.',
            'description.string' => 'Deskripsi lowongan kerja harus berupa teks.',
            'location.required' => 'Lokasi lowongan kerja wajib diisi.',
            'location.string' => 'Lokasi lowongan kerja harus berupa teks.',
            'location.max' => 'Lokasi lowongan kerja maksimal 100 karakter.',
            'type.required' => 'Tipe lowongan kerja wajib diisi.',
            'type.in' => 'Tipe lowongan kerja harus salah satu dari: full-time, part-time, contract, internship.',
            'status.in' => 'Status lowongan kerja harus salah satu dari: open, closed.',
        ];
    }
}

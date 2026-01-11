<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof User ? $employee->id : $employee;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $employeeId],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal :max karakter.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.max' => 'Email maksimal :max karakter.',
            'email.unique' => 'Email harus unik.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal :min karakter.',
        ];
    }
}

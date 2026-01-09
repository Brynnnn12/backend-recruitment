<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user dengan role admin atau hr sebagai creator vacancy
        $hrUser = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'hr']);
        })->first();

        if (!$hrUser) {
            $this->command->error('No admin/hr user found. Run RoleSeeder first.');
            return;
        }

        // Buat 10 vacancy dengan created_by user yang sudah ada
        \App\Models\Vacancy::factory(10)->create([
            'created_by' => $hrUser->id,
        ]);
    }
}

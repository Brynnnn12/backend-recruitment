<?php

use App\Models\User;
use App\Models\Vacancy;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'hr']);
    Role::create(['name' => 'user']);
});

function verifiedUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'email_verified_at' => Carbon::now(),
    ]);

    $user->assignRole($role);

    return $user;
}

function createVacancy(User $createdBy): Vacancy
{
    return Vacancy::factory()->create([
        'created_by' => $createdBy->id,
        'status' => 'open',
    ]);
}


test('admin dan hr dapat mengambil daftar lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');

    actingAs($adminUser, 'sanctum');
    $response = get('/api/vacancies');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data',
    ]);

    actingAs($hrUser, 'sanctum');
    $response = get('/api/vacancies');
    $response->assertStatus(200);
});

test('admin dan hr dapat membuat lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');

    $vacancyData = [
        'title' => 'Software Engineer',
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'full-time',
    ];

    actingAs($adminUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'id',
            'title',
            'description',
            'location',
            'type',
            'status',
            'created_by',
        ],
    ]);
    assertDatabaseHas('vacancies', [
        'title' => 'Software Engineer',
        'created_by' => $adminUser->id,
    ]);

    actingAs($hrUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(201);
    assertDatabaseHas('vacancies', [
        'title' => 'Software Engineer',
        'created_by' => $hrUser->id,
    ]);
});

test('role user tidak diizinkan membuat lowongan', function () {
    $normalUser = verifiedUserWithRole('user');

    $vacancyData = [
        'title' => 'Software Engineer',
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'full-time',
    ];

    actingAs($normalUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(403);

    assertDatabaseMissing('vacancies', [
        'title' => 'Software Engineer',
        'created_by' => $normalUser->id,
    ]);
});

test('pengguna tidak terautentikasi tidak dapat mengakses daftar lowongan', function () {
    $response = get('/api/vacancies');
    $response->assertStatus(401); // Unauthorized
});

test('pengguna tidak terautentikasi tidak dapat membuat lowongan', function () {
    $vacancyData = [
        'title' => 'Software Engineer',
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'full-time',
    ];

    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(401);
});

test('role user tidak dapat mengakses daftar lowongan', function () {
    $normalUser = verifiedUserWithRole('user');

    actingAs($normalUser, 'sanctum');
    $response = get('/api/vacancies');
    $response->assertStatus(403);
});

test('admin dan hr dapat melihat detail lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($hrUser);

    actingAs($adminUser, 'sanctum');
    $response = get("/api/vacancies/{$vacancy->id}");
    $response->assertStatus(200);
    $response->assertJsonPath('data.id', $vacancy->id);

    actingAs($hrUser, 'sanctum');
    $response = get("/api/vacancies/{$vacancy->id}");
    $response->assertStatus(200);
});

test('role user tidak dapat melihat detail lowongan', function () {
    $normalUser = verifiedUserWithRole('user');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($hrUser);

    actingAs($normalUser, 'sanctum');
    $response = get("/api/vacancies/{$vacancy->id}");
    $response->assertStatus(403);
});

test('admin dan hr dapat update lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy1 = createVacancy($adminUser);
    $vacancy2 = createVacancy($hrUser);

    $updateData = [
        'title' => 'Senior Software Engineer',
        'location' => 'Bandung',
        'status' => 'closed',
    ];

    actingAs($adminUser, 'sanctum');
    $response = putJson("/api/vacancies/{$vacancy1->id}", $updateData);
    $response->assertStatus(200);
    assertDatabaseHas('vacancies', [
        'id' => $vacancy1->id,
        'title' => 'Senior Software Engineer',
        'location' => 'Bandung',
        'status' => 'closed',
    ]);

    actingAs($hrUser, 'sanctum');
    $response = putJson("/api/vacancies/{$vacancy2->id}", $updateData);
    $response->assertStatus(200);
});

test('role user tidak dapat update lowongan', function () {
    $normalUser = verifiedUserWithRole('user');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($hrUser);

    $updateData = [
        'title' => 'Senior Software Engineer',
    ];

    actingAs($normalUser, 'sanctum');
    $response = putJson("/api/vacancies/{$vacancy->id}", $updateData);
    $response->assertStatus(403);
});

test('admin dan hr dapat delete lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy1 = createVacancy($adminUser);
    $vacancy2 = createVacancy($hrUser);

    actingAs($adminUser, 'sanctum');
    $response = deleteJson("/api/vacancies/{$vacancy1->id}");
    $response->assertStatus(200);
    assertDatabaseMissing('vacancies', ['id' => $vacancy1->id]);

    actingAs($hrUser, 'sanctum');
    $response = deleteJson("/api/vacancies/{$vacancy2->id}");
    $response->assertStatus(200);
    assertDatabaseMissing('vacancies', ['id' => $vacancy2->id]);
});

test('role user tidak dapat delete lowongan', function () {
    $normalUser = verifiedUserWithRole('user');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($hrUser);

    actingAs($normalUser, 'sanctum');
    $response = deleteJson("/api/vacancies/{$vacancy->id}");
    $response->assertStatus(403);
});

test('validation gagal jika title tidak disertakan', function () {
    $hrUser = verifiedUserWithRole('hr');

    $vacancyData = [
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'full-time',
    ];

    actingAs($hrUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['title']);
});

test('validation gagal jika type tidak valid', function () {
    $hrUser = verifiedUserWithRole('hr');

    $vacancyData = [
        'title' => 'Software Engineer',
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'invalid-type',
    ];

    actingAs($hrUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type']);
});

test('validation gagal jika status tidak valid saat update', function () {
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($hrUser);

    $updateData = [
        'status' => 'invalid-status',
    ];

    actingAs($hrUser, 'sanctum');
    $response = putJson("/api/vacancies/{$vacancy->id}", $updateData);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['status']);
});

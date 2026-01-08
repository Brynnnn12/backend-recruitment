<?php

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'hr']);
    Role::create(['name' => 'user']);

    Storage::fake('public');
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

test('admin dan hr dapat mengambil daftar applications', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');

    actingAs($adminUser, 'sanctum');
    $response = get('/api/applications');
    $response->assertStatus(200);

    actingAs($hrUser, 'sanctum');
    $response = get('/api/applications');
    $response->assertStatus(200);
});

test('user dapat mengambil daftar applications miliknya', function () {
    $user = verifiedUserWithRole('user');

    actingAs($user, 'sanctum');
    $response = get('/api/applications');
    $response->assertStatus(200);
});

test('user dapat membuat application (apply job)', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $cvFile = UploadedFile::fake()->create('cv.pdf', 1024); // 1MB

    actingAs($applicantUser, 'sanctum');
    $response = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $cvFile,
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'id',
            'user_id',
            'vacancy_id',
            'cv_file',
            'status',
            'applied_at',
        ],
    ]);

    assertDatabaseHas('applications', [
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED->value,
    ]);
});

test('admin dan hr tidak dapat membuat application (hanya user)', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $vacancy = createVacancy($adminUser);

    $cvFile = UploadedFile::fake()->create('cv.pdf', 1024);

    actingAs($adminUser, 'sanctum');
    $response = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $cvFile,
    ]);
    $response->assertStatus(403);

    actingAs($hrUser, 'sanctum');
    $response = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $cvFile,
    ]);
    $response->assertStatus(403);
});

test('user tidak dapat apply lowongan yang sama dua kali', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $cvFile1 = UploadedFile::fake()->create('cv1.pdf', 1024);
    $cvFile2 = UploadedFile::fake()->create('cv2.pdf', 1024);

    actingAs($applicantUser, 'sanctum');

    // First application - success
    $response1 = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $cvFile1,
    ]);
    $response1->assertStatus(201);

    // Second application - should fail
    $response2 = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $cvFile2,
    ]);
    $response2->assertStatus(422);
    $response2->assertJsonValidationErrors(['vacancy_id']);
});

test('user dapat melihat detail application miliknya', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($applicantUser, 'sanctum');
    $response = get("/api/applications/{$application->id}");
    $response->assertStatus(200);
    $response->assertJsonPath('data.id', $application->id);
});

test('user tidak dapat melihat application milik user lain', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicant1 = verifiedUserWithRole('user');
    $applicant2 = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicant1->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($applicant2, 'sanctum');
    $response = get("/api/applications/{$application->id}");
    $response->assertStatus(403);
});

test('admin dan hr dapat melihat semua applications', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($adminUser, 'sanctum');
    $response = get("/api/applications/{$application->id}");
    $response->assertStatus(200);

    actingAs($hrUser, 'sanctum');
    $response = get("/api/applications/{$application->id}");
    $response->assertStatus(200);
});

test('user dapat update CV application miliknya jika status masih APPLIED', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
        'cv_file' => 'cv/old-cv.pdf',
    ]);

    $newCvFile = UploadedFile::fake()->create('new-cv.pdf', 1024);

    actingAs($applicantUser, 'sanctum');
    $response = post("/api/applications/{$application->id}/update-cv", [
        'cv_file' => $newCvFile,
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('message', 'Application CV updated successfully');
});

test('user tidak dapat update CV jika status bukan APPLIED', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::REVIEWED,
        'cv_file' => 'cv/old-cv.pdf',
    ]);

    $newCvFile = UploadedFile::fake()->create('new-cv.pdf', 1024);

    actingAs($applicantUser, 'sanctum');
    $response = post("/api/applications/{$application->id}/update-cv", [
        'cv_file' => $newCvFile,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['status']);
});

test('user tidak dapat update CV application milik user lain', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicant1 = verifiedUserWithRole('user');
    $applicant2 = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicant1->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
        'cv_file' => 'cv/old-cv.pdf',
    ]);

    $newCvFile = UploadedFile::fake()->create('new-cv.pdf', 1024);

    actingAs($applicant2, 'sanctum');
    $response = post("/api/applications/{$application->id}/update-cv", [
        'cv_file' => $newCvFile,
    ]);

    $response->assertStatus(403);
});

test('admin dan hr dapat update status application', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');

    $vacancy1 = createVacancy($hrUser);
    $vacancy2 = createVacancy($hrUser); // Buat vacancy kedua

    $application1 = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy1->id, // Pakai vacancy 1
        'status' => ApplicationStatus::APPLIED,
    ]);

    $application2 = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy2->id, // Pakai vacancy 2 agar tidak bentrok
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($adminUser, 'sanctum');
    $response = putJson("/api/applications/{$application1->id}/status", [
        'status' => 'reviewed',
    ]);
    $response->assertStatus(200);
    assertDatabaseHas('applications', [
        'id' => $application1->id,
        'status' => ApplicationStatus::REVIEWED->value,
    ]);

    actingAs($hrUser, 'sanctum');
    $response = putJson("/api/applications/{$application2->id}/status", [
        'status' => 'interview',
    ]);
    $response->assertStatus(200);
    assertDatabaseHas('applications', [
        'id' => $application2->id,
        'status' => ApplicationStatus::INTERVIEW->value,
    ]);
});

test('user tidak dapat update status application', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($applicantUser, 'sanctum');
    $response = putJson("/api/applications/{$application->id}/status", [
        'status' => 'reviewed',
    ]);
    $response->assertStatus(403);
});

test('hanya admin yang dapat delete application', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');

    $vacancy1 = createVacancy($hrUser);
    $vacancy2 = createVacancy($hrUser); // Buat vacancy kedua

    $application1 = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy1->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    $application2 = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy2->id, // Pakai vacancy 2
        'status' => ApplicationStatus::APPLIED,
    ]);
    // Admin dapat delete
    actingAs($adminUser, 'sanctum');
    $response = deleteJson("/api/applications/{$application1->id}");
    $response->assertStatus(200);
    assertDatabaseMissing('applications', ['id' => $application1->id]);

    // HR tidak dapat delete
    actingAs($hrUser, 'sanctum');
    $response = deleteJson("/api/applications/{$application2->id}");
    $response->assertStatus(403);
});

test('user tidak dapat delete application', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $application = Application::factory()->create([
        'user_id' => $applicantUser->id,
        'vacancy_id' => $vacancy->id,
        'status' => ApplicationStatus::APPLIED,
    ]);

    actingAs($applicantUser, 'sanctum');
    $response = deleteJson("/api/applications/{$application->id}");
    $response->assertStatus(403);
});

test('pengguna tidak terautentikasi tidak dapat mengakses applications', function () {
    $response = get('/api/applications');
    $response->assertStatus(401);
});

test('pengguna tidak terautentikasi tidak dapat membuat application', function () {
    $cvFile = UploadedFile::fake()->create('cv.pdf', 1024);

    $response = post('/api/applications', [
        'vacancy_id' => 1,
        'cv_file' => $cvFile,
    ]);
    $response->assertStatus(401);
});

test('validation gagal jika cv_file tidak disertakan saat apply', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    actingAs($applicantUser, 'sanctum');
    $response = postJson('/api/applications', [
        'vacancy_id' => $vacancy->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['cv_file']);
});

test('validation gagal jika vacancy_id tidak valid', function () {
    $applicantUser = verifiedUserWithRole('user');
    $cvFile = UploadedFile::fake()->create('cv.pdf', 1024);

    actingAs($applicantUser, 'sanctum');
    $response = post('/api/applications', [
        'vacancy_id' => 99999, // Non-existent vacancy
        'cv_file' => $cvFile,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['vacancy_id']);
});

test('validation gagal jika cv_file bukan PDF', function () {
    $hrUser = verifiedUserWithRole('hr');
    $applicantUser = verifiedUserWithRole('user');
    $vacancy = createVacancy($hrUser);

    $docFile = UploadedFile::fake()->create('cv.docx', 1024);

    actingAs($applicantUser, 'sanctum');
    $response = post('/api/applications', [
        'vacancy_id' => $vacancy->id,
        'cv_file' => $docFile,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['cv_file']);
});

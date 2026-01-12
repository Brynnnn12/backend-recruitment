<?php

use App\Models\User;
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

function verifiedUserWithRoleForEmployee(string $role): User
{
    $user = User::factory()->create([
        'email_verified_at' => Carbon::now(),
    ]);

    $user->assignRole($role);

    return $user;
}


test('admin  dapat mengambil daftar karyawan', function () {
    $adminUser = verifiedUserWithRoleForEmployee('admin');

    actingAs($adminUser, 'sanctum');
    $response = get('/api/employees');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data',
    ]);
});

test('user dan hr tidak dapat mengambil daftar karyawan', function () {
    $normalUser = verifiedUserWithRoleForEmployee('user');
    $hrUser = verifiedUserWithRoleForEmployee('hr');

    actingAs($normalUser, 'sanctum');
    $response = get('/api/employees');
    $response->assertStatus(403);
    actingAs($hrUser, 'sanctum');
    $response = get('/api/employees');
    $response->assertStatus(403);
});


test('user yang nggak punya role tidak dapat mengambil daftar karyawan', function () {
    $normalUser = User::factory()->create([
        'email_verified_at' => Carbon::now(),
    ]);

    actingAs($normalUser, 'sanctum');
    $response = get('/api/employees');
    $response->assertStatus(403);
});


test('admin dapat melihat detail karyawan', function () {
    $adminUser = verifiedUserWithRoleForEmployee('admin');
    $hrUser = verifiedUserWithRoleForEmployee('hr');

    actingAs($adminUser, 'sanctum');
    $response = get("/api/employees/{$hrUser->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'data',
    ]);
});

test('user dan hr tidak dapat melihat detail karyawan', function () {
    $normalUser = verifiedUserWithRoleForEmployee('user');
    $hrUser1 = verifiedUserWithRoleForEmployee('hr');
    $hrUser2 = verifiedUserWithRoleForEmployee('hr');

    actingAs($normalUser, 'sanctum');
    $response = get("/api/employees/{$hrUser1->id}");
    $response->assertStatus(403);
    actingAs($hrUser1, 'sanctum');
    $response = get("/api/employees/{$hrUser2->id}");
    $response->assertStatus(403);
});


test('user yang nggak punya role tidak dapat melihat detail karyawan', function () {
    $normalUser = User::factory()->create([
        'email_verified_at' => Carbon::now(),
    ]);
    $hrUser = verifiedUserWithRoleForEmployee('hr');

    actingAs($normalUser, 'sanctum');
    $response = get("/api/employees/{$hrUser->id}");
    $response->assertStatus(403);
});


test('admin dapat membuat karyawan baru', function () {
    $adminUser = verifiedUserWithRoleForEmployee('admin');

    $employeeData = [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'password' => 'password123',
    ];



    actingAs($adminUser, 'sanctum');
    $response = postJson('/api/employees', $employeeData);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data',
    ]);
    assertDatabaseHas('users', [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
    ]);
});


test('user dan hr tidak dapat buat user baru', function () {
    $hrUser = verifiedUserWithRoleForEmployee('hr');
    $userBiasa = verifiedUserWithRoleForEmployee('user');

    $employeeData = [
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com',
        'password' => 'password123',
    ];

    actingAs($hrUser, 'sanctum');
    $response = postJson('/api/employees', $employeeData);
    $response->assertStatus(403);
    actingAs($userBiasa, 'sanctum');
    $response = postJson('/api/employees', $employeeData);
    $response->assertStatus(403);
});


test('admin bisa update user', function () {
    $adminUser = verifiedUserWithRoleForEmployee('admin');


    $employee = User::factory()->create([
        'name' => 'bryan',
        'email' => 'kamu@gmail.com',
        'password' => 'password',
    ]);

    $employee->assignRole('hr');

    actingAs($adminUser, 'sanctum');

    $response = putJson("/api/employees/{$employee->id}", [
        'name' => 'lama',
    ]);

    $response->assertStatus(200);
    assertDatabaseHas('users', [
        'id' => $employee->id,
        'name' => 'lama'
    ]);
});

test('user dan hr tidak dapat update user', function () {
    $hrUser = verifiedUserWithRoleForEmployee('hr');
    $normalUser = verifiedUserWithRoleForEmployee('user');

    $employee = User::factory()->create([
        'name' => 'bryan',
        'email' => 'bryan@example.com',
        'password' => 'password',
    ]);

    actingAs($normalUser, 'sanctum');
    $response = putJson("/api/employees/{$employee->id}", [
        'name' => 'lama',
    ]);
    $response->assertStatus(403);
    actingAs($hrUser, 'sanctum');
    $response = putJson("/api/employees/{$employee->id}", [
        'name' => 'lama',
    ]);
    $response->assertStatus(403);
});

test('admin dapat menghapus karyawan', function () {
    $adminUser = verifiedUserWithRoleForEmployee('admin');

    $employee = User::factory()->create([
        'name' => 'bryan',
        'email' => 'bryan@example.com',
        'password' => 'password',
    ]);

    $employee->assignRole('hr');

    actingAs($adminUser, 'sanctum');
    $response = deleteJson("/api/employees/{$employee->id}");
    $response->assertStatus(200);
    assertDatabaseMissing('users', [
        'id' => $employee->id,
    ]);
});
test('user dan hr tidak dapat menghapus karyawan', function () {
    $hrUser = verifiedUserWithRoleForEmployee('hr');
    $normalUser = verifiedUserWithRoleForEmployee('user');

    $employee = User::factory()->create([
        'name' => 'bryan',
        'email' => 'bryan@example.com',
        'password' => 'password',
    ]);

    actingAs($normalUser, 'sanctum');
    $response = deleteJson("/api/employees/{$employee->id}");
    $response->assertStatus(403);
    actingAs($hrUser, 'sanctum');
    $response = deleteJson("/api/employees/{$employee->id}");
    $response->assertStatus(403);
});

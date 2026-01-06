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

uses(RefreshDatabase::class);

beforeEach(function () {
    // Membuat peran admin sebelum setiap pengujian
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


test('admin dan hr dapat mengambil daftar lowongan', function () {
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');

    actingAs($adminUser, 'sanctum');
    $response = get('/api/vacancies');
    $response->assertStatus(200);

    actingAs($hrUser, 'sanctum');
    $response = get('/api/vacancies');
    $response->assertStatus(200);
});

test('admin dan hr dapat membuat lowongan', function () {
    //Arrange
    $adminUser = verifiedUserWithRole('admin');
    $hrUser = verifiedUserWithRole('hr');

    $vacancyData = [
        'title' => 'Software Engineer',
        'description' => 'We are looking for a skilled Software Engineer.',
        'location' => 'Jakarta',
        'type' => 'full-time',
    ];

    //Act & Assert for Admin
    actingAs($adminUser, 'sanctum');
    $response = postJson('/api/vacancies', $vacancyData);
    $response->assertStatus(201);
    assertDatabaseHas('vacancies', [
        'title' => 'Software Engineer',
        'created_by' => $adminUser->id,
    ]);
    //Act & Assert for HR
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

    $response = actingAs($normalUser, 'sanctum')->postJson('/api/vacancies', $vacancyData);
    //dump($response->json());
    $response->assertStatus(403); // Forbidden

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
    $response->assertStatus(401); // Unauthorized
});

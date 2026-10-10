<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthenticationAndSecureFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_and_is_redirected_to_the_intended_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_credentials_do_not_create_an_authenticated_session(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', $admin->email);

        $this->assertGuest();
    }

    public function test_logout_invalidates_session_data_and_regenerates_the_csrf_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withSession([
                '_token' => 'token-before-logout',
                'private_marker' => 'must-be-removed',
            ])
            ->post(route('logout'));

        $response->assertRedirect(route('landing'));
        $this->assertGuest();
        $this->assertFalse(session()->has('private_marker'));
        $this->assertNotSame('token-before-logout', session()->token());
    }

    public function test_access_tiers_protect_representative_admin_mutations(): void
    {
        $target = User::factory()->create(['role' => 'admin']);

        $this->put(route('admin.users.update', $target), [
            'name' => 'Unauthorized Change',
            'email' => 'changed@example.test',
            'role' => 'admin',
        ])->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->put(route('admin.users.update', $target), [
                'name' => 'Unauthorized Change',
                'email' => 'changed@example.test',
                'role' => 'admin',
            ])
            ->assertForbidden();

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)
            ->put(route('admin.users.update', $target), [
                'name' => 'Authorized Change',
                'email' => 'changed@example.test',
                'role' => 'admin',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Authorized Change',
            'email' => $target->email,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_read_public_and_private_files_through_the_secure_endpoint(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::disk('public')->put('booking/public-document.txt', 'public contents');
        Storage::disk('local')->put('requests/private-document.txt', 'private contents');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get(route('admin.secure-file', ['path' => 'booking/public-document.txt']))
            ->assertRedirect(route('login'));

        $publicResponse = $this->actingAs($admin)
            ->get(route('admin.secure-file', ['path' => 'booking/public-document.txt']))
            ->assertOk();

        $this->assertPrivateNoStoreCacheControl($publicResponse->headers->get('Cache-Control'));
        $this->assertSame('public contents', $publicResponse->baseResponse->getFile()->getContent());

        $privateResponse = $this->actingAs($admin)
            ->get(route('admin.secure-file', ['path' => 'requests/private-document.txt']))
            ->assertOk();

        $this->assertPrivateNoStoreCacheControl($privateResponse->headers->get('Cache-Control'));
        $this->assertSame('private contents', $privateResponse->baseResponse->getFile()->getContent());
    }

    private function assertPrivateNoStoreCacheControl(?string $header): void
    {
        $directives = array_map('trim', explode(',', strtolower($header ?? '')));

        $this->assertContains('private', $directives);
        $this->assertContains('no-cache', $directives);
        $this->assertContains('no-store', $directives);
        $this->assertNotContains('public', $directives);
    }

    public function test_secure_file_endpoint_rejects_missing_files_and_directory_traversal(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $outsidePath = dirname(dirname(Storage::disk('local')->path('placeholder'))).'/outside.txt';
        file_put_contents($outsidePath, 'must not be exposed');
        $admin = User::factory()->create(['role' => 'admin']);

        try {
            $this->actingAs($admin)
                ->get(route('admin.secure-file', ['path' => 'missing.pdf']))
                ->assertNotFound();

            $this->actingAs($admin)
                ->get('/admin/files/%2e%2e/outside.txt')
                ->assertNotFound();
        } finally {
            @unlink($outsidePath);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useIsolatedJsonData();
        foreach (['users', 'pembinas', 'pimpinans', 'units', 'anggotas', 'site_contents', 'publications'] as $collection) {
            app(JsonStore::class)->replace($collection, []);
        }
    }

    public function test_admin_routes_require_a_login_session(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_level_two_user_can_log_in_and_access_the_control_panel(): void
    {
        $this->createUser([
            'email' => 'admin@example.com',
            'password' => 'test-password',
            'level_akses' => 2,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'test-password',
        ])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_invalid_user_credentials_are_rejected(): void
    {
        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_registration_rejects_an_email_that_already_exists_in_json(): void
    {
        $this->createUser(['email' => 'member@example.com']);

        $this->from(route('admin.register'))
            ->post(route('admin.register.store'), [
                'name' => 'Another Member',
                'email' => 'member@example.com',
                'password' => 'member-password',
                'password_confirmation' => 'member-password',
            ])
            ->assertRedirect(route('admin.register'))
            ->assertSessionHasErrors('email');

        $this->assertCount(1, app(JsonStore::class)->all('users'));
    }

    public function test_registration_creates_a_level_one_user_even_if_form_requests_level_two(): void
    {
        $this->get(route('admin.register'))->assertOk();

        $this->post(route('admin.register.store'), [
            'name' => 'New Member',
            'email' => 'member@example.com',
            'password' => 'member-password',
            'password_confirmation' => 'member-password',
            'level_akses' => 2,
        ])->assertRedirect(route('home'));

        $member = app(JsonStore::class)->firstWhere('users', 'email', 'member@example.com');
        $this->assertNotNull($member);
        $this->assertSame(1, $member->level_akses);
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_level_one_user_can_log_in_but_cannot_access_the_control_panel(): void
    {
        $this->createUser([
            'email' => 'member@example.com',
            'password' => 'test-password',
            'level_akses' => 1,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'member@example.com',
            'password' => 'test-password',
        ])->assertRedirect(route('home'));

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_logout_clears_the_admin_session(): void
    {
        $user = $this->createUser(['level_akses' => 2]);

        $this->actingAs($user)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    private function createUser(array $attributes = []): User
    {
        $record = app(JsonStore::class)->create('users', [
            'name' => $attributes['name'] ?? 'Test User',
            'email' => $attributes['email'] ?? 'admin@example.com',
            'password' => Hash::make($attributes['password'] ?? 'test-password'),
            'level_akses' => $attributes['level_akses'] ?? 1,
            'remember_token' => null,
        ]);
        $user = new User;
        $user->forceFill((array) $record);

        return $user;
    }
}

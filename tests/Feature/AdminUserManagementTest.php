<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useIsolatedJsonData();
        foreach (['users', 'pembinas', 'pimpinans', 'units', 'anggotas', 'site_contents', 'publications'] as $collection) {
            app(JsonStore::class)->replace($collection, []);
        }
    }

    public function test_admin_can_create_update_and_delete_users(): void
    {
        $admin = $this->createUser('admin@example.com', 2);
        $this->actingAs($admin);

        $this->get(route('admin.dashboard', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('Daftar Pengguna')
            ->assertSee('admin@example.com')
            ->assertDontSee($admin->password);

        $this->post(route('admin.users.store'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota@example.com',
            'password' => 'member-password',
            'password_confirmation' => 'member-password',
            'level_akses' => 1,
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'users']));

        $member = app(JsonStore::class)->firstWhere('users', 'email', 'anggota@example.com');
        $this->assertNotNull($member);
        $this->assertSame(1, $member->level_akses);
        $this->assertTrue(Hash::check('member-password', $member->password));

        $this->put(route('admin.users.update', $member->id), [
            'name' => 'Nama Diperbarui',
            'email' => 'anggota@example.com',
            'password' => '',
            'password_confirmation' => '',
            'level_akses' => 1,
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'users']));

        $updatedWithoutPassword = app(JsonStore::class)->find('users', $member->id);
        $this->assertSame('Nama Diperbarui', $updatedWithoutPassword->name);
        $this->assertTrue(Hash::check('member-password', $updatedWithoutPassword->password));

        $this->put(route('admin.users.update', $member->id), [
            'name' => 'Anggota Diperbarui',
            'email' => 'anggota.baru@example.com',
            'password' => 'new-member-password',
            'password_confirmation' => 'new-member-password',
            'level_akses' => 2,
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'users']));

        $updated = app(JsonStore::class)->find('users', $member->id);
        $this->assertSame('Anggota Diperbarui', $updated->name);
        $this->assertSame('anggota.baru@example.com', $updated->email);
        $this->assertSame(2, $updated->level_akses);
        $this->assertTrue(Hash::check('new-member-password', $updated->password));

        $this->delete(route('admin.users.destroy', $member->id))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'users']));

        $this->assertNull(app(JsonStore::class)->find('users', $member->id));
    }

    public function test_admin_cannot_demote_or_delete_the_account_in_use(): void
    {
        $admin = $this->createUser('admin@example.com', 2);
        $this->actingAs($admin);

        $this->from(route('admin.dashboard', ['tab' => 'users']))
            ->put(route('admin.users.update', $admin->id), [
                'name' => $admin->name,
                'email' => $admin->email,
                'level_akses' => 1,
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'users']))
            ->assertSessionHas('error', 'Anda tidak dapat menurunkan level akses akun yang sedang digunakan.');

        $this->from(route('admin.dashboard', ['tab' => 'users']))
            ->delete(route('admin.users.destroy', $admin->id))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'users']))
            ->assertSessionHas('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');

        $storedAdmin = app(JsonStore::class)->find('users', $admin->id);
        $this->assertSame(2, $storedAdmin->level_akses);
    }

    public function test_duplicate_email_is_rejected_when_creating_or_updating_a_user(): void
    {
        $admin = $this->createUser('admin@example.com', 2);
        $member = app(JsonStore::class)->create('users', [
            'name' => 'Anggota',
            'email' => 'anggota@example.com',
            'password' => Hash::make('member-password'),
            'level_akses' => 1,
            'remember_token' => null,
        ]);
        $this->actingAs($admin);

        $this->from(route('admin.dashboard', ['tab' => 'users']))
            ->post(route('admin.users.store'), [
                'name' => 'Duplikat',
                'email' => 'admin@example.com',
                'password' => 'new-member-password',
                'password_confirmation' => 'new-member-password',
                'level_akses' => 1,
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'users']))
            ->assertSessionHasErrors('email');

        $this->from(route('admin.dashboard', ['tab' => 'users']))
            ->put(route('admin.users.update', $member->id), [
                'name' => 'Anggota',
                'email' => 'admin@example.com',
                'level_akses' => 1,
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'users']))
            ->assertSessionHasErrors('email');

        $this->assertCount(2, app(JsonStore::class)->all('users'));
        $this->assertSame('anggota@example.com', app(JsonStore::class)->find('users', $member->id)->email);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $member = $this->createUser('member@example.com', 1);
        $this->actingAs($member)
            ->post(route('admin.users.store'), [
                'name' => 'Unauthorised',
                'email' => 'unauthorised@example.com',
                'password' => 'member-password',
                'password_confirmation' => 'member-password',
                'level_akses' => 2,
            ])
            ->assertForbidden();

        $this->assertCount(1, app(JsonStore::class)->all('users'));
    }

    private function createUser(string $email, int $level): User
    {
        $record = app(JsonStore::class)->create('users', [
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('test-password'),
            'level_akses' => $level,
            'remember_token' => null,
        ]);
        $user = new User;
        $user->forceFill((array) $record);

        return $user;
    }
}

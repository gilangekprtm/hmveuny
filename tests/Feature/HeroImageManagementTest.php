<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use Tests\TestCase;

class HeroImageManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useIsolatedJsonData();
        foreach (['users', 'pembinas', 'pimpinans', 'units', 'anggotas', 'site_contents', 'publications'] as $collection) {
            app(JsonStore::class)->replace($collection, []);
        }
    }

    public function test_admin_can_upload_replace_and_delete_the_homepage_hero_image(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $storedFiles = [];

        try {
            $this->put(route('admin.content.update'), [
                'foto' => UploadedFile::fake()->image('anggota.png'),
            ])->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

            $content = app(JsonStore::class)->first('site_contents');
            $this->assertStringStartsWith('uploads/hero/', $content->hero_image);
            $firstImage = public_path($content->hero_image);
            $this->assertFileExists($firstImage);
            $storedFiles[] = $firstImage;

            $this->get(route('home'))
                ->assertOk()
                ->assertSee('alt="Foto bersama anggota HMVE"', false)
                ->assertSee(asset($content->hero_image), false);

            $this->get(route('admin.dashboard', ['tab' => 'konten']))
                ->assertOk()
                ->assertSee(asset($content->hero_image), false)
                ->assertSee('Hapus foto hero saat menyimpan');

            $this->put(route('admin.content.update'), [
                'foto' => UploadedFile::fake()->image('anggota-baru.webp'),
            ])->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

            $updatedContent = app(JsonStore::class)->first('site_contents');
            $this->assertNotSame($content->hero_image, $updatedContent->hero_image);
            $secondImage = public_path($updatedContent->hero_image);
            $this->assertFileExists($secondImage);
            $this->assertFileDoesNotExist($firstImage);
            $storedFiles[] = $secondImage;

            $this->put(route('admin.content.update'), [
                'remove_hero_image' => '1',
            ])->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

            $this->assertNull(app(JsonStore::class)->first('site_contents')->hero_image);
            $this->assertFileDoesNotExist($secondImage);
            $this->get(route('home'))->assertDontSee('alt="Foto bersama anggota HMVE"', false);
        } finally {
            foreach ($storedFiles as $storedFile) {
                if (is_file($storedFile)) {
                    unlink($storedFile);
                }
            }
        }
    }

    public function test_hero_upload_rejects_non_image_files_without_changing_content(): void
    {
        $this->actingAs($this->createAdmin());

        $this->from(route('admin.dashboard', ['tab' => 'konten']))
            ->put(route('admin.content.update'), [
                'foto' => UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']))
            ->assertSessionHasErrors('foto');

        $this->assertNull(app(JsonStore::class)->first('site_contents'));
    }

    public function test_guests_cannot_upload_a_hero_image(): void
    {
        $this->put(route('admin.content.update'), [
            'foto' => UploadedFile::fake()->image('anggota.png'),
        ])->assertRedirect(route('admin.login'));

        $this->assertNull(app(JsonStore::class)->first('site_contents'));
    }

    public function test_admin_can_save_a_hero_image_from_vercel_blob(): void
    {
        config(['uploads.driver' => 'vercel']);
        $this->actingAs($this->createAdmin());
        $blobUrl = 'https://store.public.blob.vercel-storage.com/uploads/hero/hero-image.png';

        $this->put(route('admin.content.update'), ['foto_url' => $blobUrl])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

        $this->assertSame($blobUrl, app(JsonStore::class)->first('site_contents')->hero_image);
        $this->get(route('home'))->assertOk()->assertSee($blobUrl, false);
    }

    public function test_admin_cannot_save_a_hero_image_from_an_untrusted_host(): void
    {
        config(['uploads.driver' => 'vercel']);
        $this->actingAs($this->createAdmin());

        $this->from(route('admin.dashboard', ['tab' => 'konten']))
            ->put(route('admin.content.update'), [
                'foto_url' => 'https://example.com/uploads/hero/hero-image.png',
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']))
            ->assertSessionHasErrors('foto');

        $this->assertNull(app(JsonStore::class)->first('site_contents'));
    }

    public function test_blob_upload_authorization_requires_an_admin_session(): void
    {
        $this->get('/control-panel/blob-upload-authorize')->assertRedirect(route('admin.login'));

        $this->actingAs($this->createAdmin())
            ->get('/control-panel/blob-upload-authorize')
            ->assertNoContent();
    }

    public function test_regular_member_cannot_authorize_a_blob_upload(): void
    {
        $record = app(JsonStore::class)->create('users', [
            'name' => 'Member HMVE',
            'email' => 'member@example.com',
            'password' => Hash::make('test-password'),
            'level_akses' => 1,
        ]);
        $member = new User;
        $member->forceFill((array) $record);

        $this->actingAs($member)
            ->get('/control-panel/blob-upload-authorize')
            ->assertForbidden();
    }

    public function test_replacing_a_vercel_hero_image_deletes_the_previous_blob(): void
    {
        $previousUrl = 'https://store.public.blob.vercel-storage.com/uploads/hero/old-image.png';
        $nextUrl = 'https://store.public.blob.vercel-storage.com/uploads/hero/new-image.png';
        config([
            'uploads.driver' => 'vercel',
            'services.vercel_blob.internal_token' => 'test-internal-token',
            'services.vercel_blob.deployment_url' => 'hmve.vercel.app',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://hmve.vercel.app/api/blob-delete' => Http::response(null, 204),
        ]);
        app(JsonStore::class)->create('site_contents', ['hero_image' => $previousUrl]);
        $this->actingAs($this->createAdmin());

        $this->put(route('admin.content.update'), ['foto_url' => $nextUrl])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

        $this->assertSame($nextUrl, app(JsonStore::class)->first('site_contents')->hero_image);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://hmve.vercel.app/api/blob-delete'
            && $request->hasHeader('Authorization', 'Bearer test-internal-token')
            && $request->data()['url'] === $previousUrl);
    }

    private function createAdmin(): User
    {
        $record = app(JsonStore::class)->create('users', [
            'name' => 'Admin HMVE',
            'email' => 'admin@example.com',
            'password' => Hash::make('test-password'),
            'level_akses' => 2,
            'remember_token' => null,
        ]);
        $admin = new User;
        $admin->forceFill((array) $record);

        return $admin;
    }
}

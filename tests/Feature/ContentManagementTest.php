<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useIsolatedJsonData();
        foreach (['users', 'pembinas', 'pimpinans', 'units', 'anggotas', 'site_contents', 'publications'] as $collection) {
            app(JsonStore::class)->replace($collection, []);
        }
    }

    public function test_admin_can_manage_site_content_and_publications_shown_on_homepage(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->put(route('admin.content.update'), [
                'sejarah' => 'Sejarah singkat HMVE',
                'visi' => 'Menjadi wadah mahasiswa elektro.',
                'misi' => 'Mengembangkan kompetensi anggota.',
                'join_title' => 'Bergabung dengan HMVE',
                'join_description' => 'Pendaftaran anggota baru.',
                'join_label' => 'Daftar',
                'join_url' => 'https://example.com/daftar',
                'contact_email' => 'hmve@example.com',
                'contact_phone' => '081234567890',
                'contact_address' => 'Yogyakarta',
                'instagram_url' => 'https://instagram.com/hmve',
            ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

        $this->post(route('admin.publication.store'), [
            'kategori' => 'Karya & Prestasi',
            'judul' => 'Prototipe Monitoring Energi',
            'deskripsi' => 'Karya anggota HMVE.',
            'tanggal' => '2026-10-04',
        ])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'publikasi']));

        $publication = app(JsonStore::class)->all('publications')->first();

        $siteContent = app(JsonStore::class)->first('site_contents');
        $this->assertSame('Sejarah singkat HMVE', $siteContent->sejarah);
        $this->assertSame('hmve@example.com', $siteContent->contact_email);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sejarah singkat HMVE')
            ->assertSee('Prototipe Monitoring Energi')
            ->assertSee('hmve@example.com');

        $this->put(route('admin.publication.update', $publication->id), [
            'kategori' => 'Berita',
            'judul' => 'Pembaruan HMVE',
            'deskripsi' => 'Informasi terbaru.',
            'tanggal' => '2026-10-04',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'publikasi']));

        $updatedPublication = app(JsonStore::class)->find('publications', $publication->id);
        $this->assertSame('Pembaruan HMVE', $updatedPublication->judul);

        $this->delete(route('admin.publication.destroy', $publication->id))
            ->assertRedirect(route('admin.dashboard', ['tab' => 'publikasi']));

        $this->assertNull(app(JsonStore::class)->find('publications', $publication->id));
    }

    public function test_admin_can_toggle_join_menu_visibility(): void
    {
        $admin = $this->createAdmin();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Bergabung dengan HMVE');

        $this->actingAs($admin)
            ->put(route('admin.content.update'), ['show_join_menu' => '1'])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

        $this->assertTrue((bool) app(JsonStore::class)->first('site_contents')->show_join_menu);
        $this->get(route('home'))
            ->assertSee('Bergabung dengan HMVE')
            ->assertSee(route('join'));

        $this->put(route('admin.content.update'), [])
            ->assertRedirect(route('admin.dashboard', ['tab' => 'konten']));

        $this->assertFalse((bool) app(JsonStore::class)->first('site_contents')->show_join_menu);
        $this->get(route('home'))->assertDontSee('Bergabung dengan HMVE');
    }

    public function test_admin_can_save_and_display_programs_of_study_for_leaders_and_members(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $unit = app(JsonStore::class)->create('units', [
            'nama' => 'Divisi Pengujian',
            'slug' => 'divisi-pengujian',
            'kategori' => 'Divisi',
            'deskripsi' => 'Unit untuk pengujian.',
        ]);

        $this->get(route('admin.dashboard', ['tab' => 'pimpinan']))
            ->assertOk()
            ->assertSee('Program Studi (Opsional)');
        $this->get(route('admin.dashboard', ['tab' => 'anggota']))
            ->assertOk()
            ->assertSee('Program Studi (Opsional)');

        $this->post(route('admin.pimpinan.store'), [
            'nama' => 'Ketua Pengujian',
            'posisi' => 'Ketua Himpunan',
            'prodi' => 'Teknik Elektro',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'pimpinan']));

        $this->post(route('admin.anggota.store'), [
            'unit_id' => $unit->id,
            'nama' => 'Kepala Pengujian',
            'jabatan' => 'Kepala Divisi',
            'prodi' => 'Teknik Elektronika',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'anggota']));

        $this->post(route('admin.anggota.store'), [
            'unit_id' => $unit->id,
            'nama' => 'Staf Pengujian',
            'jabatan' => 'Staff',
            'prodi' => 'Teknik Informatika',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'anggota']));

        $president = app(JsonStore::class)->first('pimpinans');
        $members = app(JsonStore::class)->all('anggotas');
        $this->assertSame('Teknik Elektro', $president->prodi);
        $this->assertSame('Teknik Elektronika', $members->first()->prodi);
        $this->assertSame('Teknik Informatika', $members->last()->prodi);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Teknik Elektro')
            ->assertSee('Teknik Elektronika');

        $this->get(route('unit.detail', $unit->slug))
            ->assertOk()
            ->assertSee('Teknik Elektronika')
            ->assertSee('Teknik Informatika');
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

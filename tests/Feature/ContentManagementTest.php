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

    public function test_admin_can_edit_president_and_unit_leader_and_staff_records(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $unit = app(JsonStore::class)->create('units', [
            'nama' => 'Biro Pengujian',
            'slug' => 'biro-pengujian',
            'kategori' => 'Biro',
            'deskripsi' => 'Biro untuk pengujian.',
        ]);
        $otherUnit = app(JsonStore::class)->create('units', [
            'nama' => 'Divisi Pengujian',
            'slug' => 'divisi-pengujian',
            'kategori' => 'Divisi',
            'deskripsi' => 'Divisi untuk pengujian.',
        ]);
        $president = app(JsonStore::class)->create('pimpinans', [
            'nama' => 'Ketua Lama',
            'posisi' => 'Ketua Himpunan',
            'prodi' => 'Prodi Lama',
            'image_url' => 'uploads/pimpinan/current.webp',
        ]);
        $leader = app(JsonStore::class)->create('anggotas', [
            'unit_id' => $unit->id,
            'nama' => 'Kepala Lama',
            'jabatan' => 'Kepala Biro',
            'prodi' => 'Prodi Lama',
            'deskripsi' => 'Deskripsi lama kepala.',
            'image_url' => 'uploads/anggota/leader.webp',
        ]);
        $staff = app(JsonStore::class)->create('anggotas', [
            'unit_id' => $unit->id,
            'nama' => 'Staf Lama',
            'jabatan' => 'Staff',
            'prodi' => 'Prodi Lama',
            'deskripsi' => 'Deskripsi lama staf.',
            'image_url' => 'uploads/anggota/staff.webp',
        ]);

        $this->get(route('admin.dashboard', ['tab' => 'pimpinan', 'edit_pimpinan' => $president->id]))
            ->assertOk()
            ->assertSee('Edit Ketua / Wakil')
            ->assertSee('value="Ketua Lama"', false)
            ->assertSee('value="Prodi Lama"', false)
            ->assertSee(route('admin.pimpinan.update', $president->id));
        $this->get(route('admin.dashboard', ['tab' => 'anggota', 'edit_anggota' => $leader->id]))
            ->assertOk()
            ->assertSee('Edit Anggota Biro / Divisi')
            ->assertSee('value="Kepala Lama"', false)
            ->assertSee(route('admin.anggota.update', $leader->id));
        $this->get(route('admin.dashboard', ['tab' => 'anggota']))
            ->assertSee(route('admin.dashboard', ['tab' => 'anggota', 'edit_anggota' => $staff->id]));

        $this->put(route('admin.pimpinan.update', $president->id), [
            'nama' => 'Ketua Baru',
            'posisi' => 'Wakil Ketua Himpunan',
            'prodi' => 'Teknik Elektro',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'pimpinan']));

        $this->put(route('admin.anggota.update', $leader->id), [
            'unit_id' => $otherUnit->id,
            'nama' => 'Kepala Baru',
            'jabatan' => 'Kepala Divisi',
            'prodi' => 'Teknik Elektronika',
            'deskripsi' => 'Deskripsi baru kepala.',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'anggota']));

        $this->put(route('admin.anggota.update', $staff->id), [
            'unit_id' => $unit->id,
            'nama' => 'Staf Baru',
            'jabatan' => 'Staff Administrasi',
            'prodi' => 'Teknik Informatika',
            'deskripsi' => 'Deskripsi baru staf.',
        ])->assertRedirect(route('admin.dashboard', ['tab' => 'anggota']));

        $updatedPresident = app(JsonStore::class)->find('pimpinans', $president->id);
        $this->assertSame('Ketua Baru', $updatedPresident->nama);
        $this->assertSame('Wakil Ketua Himpunan', $updatedPresident->posisi);
        $this->assertSame('Teknik Elektro', $updatedPresident->prodi);
        $this->assertSame('uploads/pimpinan/current.webp', $updatedPresident->image_url);

        $updatedLeader = app(JsonStore::class)->find('anggotas', $leader->id);
        $this->assertSame('Kepala Baru', $updatedLeader->nama);
        $this->assertSame('Kepala Divisi', $updatedLeader->jabatan);
        $this->assertSame((int) $otherUnit->id, $updatedLeader->unit_id);
        $this->assertSame('Teknik Elektronika', $updatedLeader->prodi);
        $this->assertSame('Deskripsi baru kepala.', $updatedLeader->deskripsi);
        $this->assertSame('uploads/anggota/leader.webp', $updatedLeader->image_url);

        $updatedStaff = app(JsonStore::class)->find('anggotas', $staff->id);
        $this->assertSame('Staf Baru', $updatedStaff->nama);
        $this->assertSame('Staff Administrasi', $updatedStaff->jabatan);
        $this->assertSame('Teknik Informatika', $updatedStaff->prodi);
        $this->assertSame('Deskripsi baru staf.', $updatedStaff->deskripsi);
        $this->assertSame('uploads/anggota/staff.webp', $updatedStaff->image_url);

        $this->get(route('unit.detail', $otherUnit->slug))
            ->assertOk()
            ->assertSee('Kepala Baru')
            ->assertSee('Teknik Elektronika');
        $this->get(route('unit.detail', $unit->slug))
            ->assertOk()
            ->assertSee('Staf Baru')
            ->assertSee('Teknik Informatika');
    }

    public function test_admin_cannot_update_missing_pimpinan_or_anggota_records(): void
    {
        $this->actingAs($this->createAdmin());

        $this->put(route('admin.pimpinan.update', 999), [
            'nama' => 'Tidak Ada',
            'posisi' => 'Ketua Himpunan',
        ])->assertNotFound();

        $this->put(route('admin.anggota.update', 999), [
            'unit_id' => 1,
            'nama' => 'Tidak Ada',
            'jabatan' => 'Staff',
        ])->assertNotFound();
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

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\JsonStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->useIsolatedJsonData();
        foreach (['users', 'pembinas', 'pimpinans', 'units', 'anggotas', 'site_contents', 'publications'] as $collection) {
            app(JsonStore::class)->replace($collection, []);
        }

        $record = app(JsonStore::class)->create('users', [
            'name' => 'Admin HMVE',
            'email' => 'admin@example.com',
            'password' => Hash::make('test-password'),
            'level_akses' => 2,
        ]);
        $admin = new User;
        $admin->forceFill((array) $record);
        $this->actingAs($admin);
    }

    public function test_uploaded_photos_are_saved_for_each_admin_record(): void
    {
        $unit = app(JsonStore::class)->create('units', [
            'nama' => 'Unit Test',
            'slug' => 'unit-test',
            'kategori' => 'Biro',
            'deskripsi' => 'Unit untuk pengujian.',
        ]);

        $uploads = [
            [
                'route' => 'admin.pembina.store',
                'tab' => 'pembina',
                'folder' => 'pembina',
                'collection' => 'pembinas',
                'data' => ['nama' => 'Pembina Test', 'jabatan' => 'Pembina'],
            ],
            [
                'route' => 'admin.pimpinan.store',
                'tab' => 'pimpinan',
                'folder' => 'pimpinan',
                'collection' => 'pimpinans',
                'data' => ['nama' => 'Pimpinan Test', 'posisi' => 'Ketua Himpunan'],
            ],
            [
                'route' => 'admin.anggota.store',
                'tab' => 'anggota',
                'folder' => 'anggota',
                'collection' => 'anggotas',
                'data' => [
                    'unit_id' => $unit->id,
                    'nama' => 'Anggota Test',
                    'jabatan' => 'Staff',
                ],
            ],
        ];

        $storedFiles = [];

        try {
            foreach ($uploads as $upload) {
                $response = $this->post(route($upload['route']), $upload['data'] + [
                    'foto' => UploadedFile::fake()->createWithContent(
                        'foto.webp',
                        base64_decode('UklGRhwAAABXRUJQVlA4TA8AAAAvAAAAEAcQ/Y8CBiKi/wEA', true),
                    ),
                ]);

                $response->assertRedirect(route('admin.dashboard', ['tab' => $upload['tab']]));

                $record = app(JsonStore::class)->all($upload['collection'])->last();
                $this->assertStringStartsWith('uploads/'.$upload['folder'].'/', $record->image_url);

                $storedFile = public_path($record->image_url);
                $this->assertFileExists($storedFile);
                $storedFiles[] = $storedFile;
            }
        } finally {
            foreach ($storedFiles as $storedFile) {
                if (is_file($storedFile)) {
                    unlink($storedFile);
                }
            }
        }
    }
}

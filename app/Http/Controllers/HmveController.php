<?php

namespace App\Http\Controllers;

use App\Support\JsonStore;
use App\Support\VercelBlobStore;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HmveController extends Controller
{
    public function __construct(
        private JsonStore $data,
        private VercelBlobStore $blobStore,
    ) {}

    public function index()
    {
        $pembinas = $this->data->all('pembinas');
        $pimpinans = $this->data->all('pimpinans');
        $biros = $this->unitsWithMembers('Biro');
        $divisis = $this->unitsWithMembers('Divisi');
        $unitLeaders = $biros->concat($divisis)->mapWithKeys(function (object $unit) {
            $roles = $this->classifyUnitMembers($unit->anggotas);

            return [$unit->id => $roles['kepala']->concat($roles['wakil'])];
        });
        $siteContent = $this->data->first('site_contents');
        $publications = $this->publications()->take(3);

        return view('frontend.home', compact(
            'pembinas',
            'pimpinans',
            'biros',
            'divisis',
            'unitLeaders',
            'siteContent',
            'publications',
        ));
    }

    public function join()
    {
        $siteContent = $this->data->first('site_contents');

        return view('frontend.join', compact('siteContent'));
    }

    public function detailUnit(string $slug)
    {
        $unit = $this->data->all('units')->firstWhere('slug', $slug);
        abort_if($unit === null, 404);
        $members = $this->membersForUnit($unit->id);
        $roles = $this->classifyUnitMembers($members);

        return view('frontend.unit-detail', array_merge(['unit' => $unit], $roles));
    }

    private function uploadFoto(Request $request, string $folder): ?string
    {
        if (config('uploads.driver') === 'vercel') {
            if ($request->hasFile('foto')) {
                throw ValidationException::withMessages([
                    'foto' => 'Unggah gambar melalui browser sebelum menyimpan formulir.',
                ]);
            }

            $url = $request->input('foto_url');

            if (! is_string($url) || trim($url) === '') {
                return null;
            }

            $validatedUrl = Validator::make(
                ['foto_url' => $url],
                ['foto_url' => ['required', 'url', 'max:2048']],
            )->validate()['foto_url'];

            if (! $this->blobStore->ownsUrl($validatedUrl, $folder)) {
                throw ValidationException::withMessages([
                    'foto' => 'URL gambar tidak berasal dari penyimpanan gambar HMVE.',
                ]);
            }

            return $validatedUrl;
        }

        if (! $request->hasFile('foto')) {
            return null;
        }

        $file = $request->file('foto');
        $filename = time().'_'.uniqid().'.'.$file->extension();
        $destinationPath = public_path('uploads/'.$folder);

        if (! is_dir($destinationPath) && ! mkdir($destinationPath, 0755, true) && ! is_dir($destinationPath)) {
            throw new \RuntimeException("Unable to create upload directory [{$destinationPath}].");
        }

        $file->move($destinationPath, $filename);

        return 'uploads/'.$folder.'/'.$filename;
    }

    private function classifyUnitMembers(Collection $members): array
    {
        $memberRole = fn (object $member) => $this->memberRole($member);

        return [
            'kepala' => $members->filter(fn (object $member) => $memberRole($member) === 'kepala')->values(),
            'wakil' => $members->filter(fn (object $member) => $memberRole($member) === 'wakil')->values(),
            'staff' => $members->filter(fn (object $member) => $memberRole($member) === 'staff')->values(),
        ];
    }

    private function memberRole(object $member): string
    {
        $jabatan = Str::lower($member->jabatan ?? '');

        if (Str::contains($jabatan, 'wakil')) {
            return 'wakil';
        }

        if (Str::contains($jabatan, 'kepala')) {
            return 'kepala';
        }

        return 'staff';
    }

    public function adminDashboard(Request $request)
    {
        $tab = $request->query('tab', 'unit');
        $pembinas = $this->data->all('pembinas');
        $pimpinans = $this->data->all('pimpinans');
        $units = $this->data->all('units');
        $anggotas = $this->data->all('anggotas')->map(function (object $anggota) {
            $anggota->unit = $this->data->find('units', $anggota->unit_id);

            return $anggota;
        });
        $siteContent = $this->data->first('site_contents')
            ?? (object) array_fill_keys([
                'sejarah', 'visi', 'misi', 'join_title', 'join_description', 'join_label', 'join_url',
                'show_join_menu', 'contact_email', 'contact_phone', 'contact_address', 'instagram_url',
                'youtube_url', 'tiktok_url', 'hero_image',
            ], null);
        $editingUnit = $request->query('edit_unit')
            ? $this->data->find('units', $request->query('edit_unit'))
            : null;
        $editingPimpinan = $request->query('edit_pimpinan')
            ? $this->data->find('pimpinans', $request->query('edit_pimpinan'))
            : null;
        $editingAnggota = $request->query('edit_anggota')
            ? $this->data->find('anggotas', $request->query('edit_anggota'))
            : null;
        $publications = $this->publications();
        $editingPublication = $request->query('edit')
            ? $this->data->find('publications', $request->query('edit'))
            : null;
        $users = $this->data->all('users')->map(fn (object $user) => (object) [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level_akses' => (int) $user->level_akses,
        ]);
        $editingUserRecord = $request->query('edit_user')
            ? $this->data->find('users', $request->query('edit_user'))
            : null;
        $editingUser = $editingUserRecord ? (object) [
            'id' => $editingUserRecord->id,
            'name' => $editingUserRecord->name,
            'email' => $editingUserRecord->email,
            'level_akses' => (int) $editingUserRecord->level_akses,
        ] : null;
        $currentUserId = (int) Auth::id();

        if ($editingPublication?->tanggal) {
            $editingPublication->tanggal = Carbon::parse($editingPublication->tanggal);
        }

        return view('admin.dashboard', compact(
            'pembinas',
            'pimpinans',
            'units',
            'anggotas',
            'tab',
            'siteContent',
            'publications',
            'editingPublication',
            'editingUnit',
            'editingPimpinan',
            'editingAnggota',
            'users',
            'editingUser',
            'currentUserId',
        ));
    }

    public function updateSiteContent(Request $request)
    {
        $validated = $request->validate([
            'sejarah' => 'nullable|string|max:10000',
            'visi' => 'nullable|string|max:2000',
            'misi' => 'nullable|string|max:5000',
            'join_title' => 'nullable|string|max:255',
            'join_description' => 'nullable|string|max:2000',
            'join_label' => 'nullable|string|max:80',
            'join_url' => 'nullable|url|max:255',
            'show_join_menu' => 'sometimes|boolean',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:40',
            'contact_address' => 'nullable|string|max:1000',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10240',
            'remove_hero_image' => 'sometimes|boolean',
        ]);

        $validated['show_join_menu'] = $request->boolean('show_join_menu');
        $removeHeroImage = $request->boolean('remove_hero_image');
        unset($validated['foto'], $validated['remove_hero_image']);
        $siteContent = $this->data->first('site_contents');
        $previousHeroImage = $siteContent?->hero_image;
        $newHeroImage = $this->uploadFoto($request, 'hero');

        if ($newHeroImage !== null) {
            $validated['hero_image'] = $newHeroImage;
        } elseif ($removeHeroImage) {
            $validated['hero_image'] = null;
        }

        if ($siteContent) {
            $this->data->update('site_contents', $siteContent->id, $validated);
        } else {
            $this->data->create('site_contents', $validated);
        }

        if ($newHeroImage !== null || $removeHeroImage) {
            $this->deleteHeroPhoto($previousHeroImage);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'konten'])->with('success', 'Konten website berhasil diperbarui.');
    }

    private function deleteHeroPhoto(?string $path): void
    {
        if ($path && $this->blobStore->ownsUrl($path, 'hero')) {
            $this->blobStore->delete($path);

            return;
        }

        if (! $path || ! preg_match('~\Auploads/hero/[A-Za-z0-9._-]+\z~D', $path)) {
            return;
        }

        $absolutePath = public_path($path);

        if (is_file($absolutePath) && ! unlink($absolutePath)) {
            throw new \RuntimeException("Unable to delete hero image [{$absolutePath}].");
        }
    }

    public function storePublication(Request $request)
    {
        $validated = $this->validatePublication($request);
        unset($validated['foto']);
        $validated['image_url'] = $this->uploadFoto($request, 'publikasi');

        $this->data->create('publications', $validated);

        return redirect()->route('admin.dashboard', ['tab' => 'publikasi'])->with('success', 'Publikasi berhasil ditambahkan.');
    }

    public function updatePublication(Request $request, int $publication)
    {
        $existing = $this->data->find('publications', $publication);
        abort_if($existing === null, 404);

        $validated = $this->validatePublication($request);
        unset($validated['foto']);
        $validated['image_url'] = $this->uploadFoto($request, 'publikasi') ?? $existing->image_url;
        $this->data->update('publications', $publication, $validated);

        return redirect()->route('admin.dashboard', ['tab' => 'publikasi'])->with('success', 'Publikasi berhasil diperbarui.');
    }

    public function destroyPublication(int $publication)
    {
        abort_unless($this->data->delete('publications', $publication), 404);

        return redirect()->route('admin.dashboard', ['tab' => 'publikasi'])->with('success', 'Publikasi berhasil dihapus.');
    }

    private function validatePublication(Request $request): array
    {
        return $request->validate([
            'kategori' => 'required|in:Berita,Kegiatan,Program Kerja,Karya & Prestasi',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:20000',
            'tanggal' => 'nullable|date',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);
    }

    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:Biro,Divisi',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $slug = Str::slug($validated['nama']);
        $baseSlug = $slug;
        $suffix = 2;

        while ($this->data->firstWhere('units', 'slug', $slug)) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        $unitAttributes = [
            'nama' => $validated['nama'],
            'slug' => $slug,
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'],
            'foto_bersama' => $this->uploadFoto($request, 'unit'),
        ];

        try {
            $this->data->createUnique('units', 'slug', $slug, $unitAttributes);
        } catch (DomainException) {
            throw ValidationException::withMessages([
                'nama' => 'Nama tersebut sudah digunakan oleh Biro atau Divisi lain.',
            ]);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'unit'])->with('success', 'Biro/Divisi berhasil ditambahkan!');
    }

    public function updateUnit(Request $request, int $unit)
    {
        $existing = $this->data->find('units', $unit);
        abort_if($existing === null, 404);

        $validated = $request->validate([
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->update('units', $unit, [
            'deskripsi' => $validated['deskripsi'],
            'foto_bersama' => $this->uploadFoto($request, 'unit') ?? $existing->foto_bersama,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'unit'])->with('success', 'Detail Biro/Divisi berhasil diperbarui!');
    }

    public function destroyUnit(int $unit)
    {
        abort_unless($this->data->delete('units', $unit), 404);
        $members = $this->data->all('anggotas')->where('unit_id', $unit);

        foreach ($members as $member) {
            $this->data->delete('anggotas', $member->id);
        }

        return redirect()->route('admin.dashboard', ['tab' => 'unit'])->with('success', 'Data berhasil dihapus!');
    }

    public function storePembina(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->create('pembinas', [
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'image_url' => $this->uploadFoto($request, 'pembina'),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'pembina'])->with('success', 'Data Pembina berhasil ditambahkan!');
    }

    public function destroyPembina(int $id)
    {
        abort_unless($this->data->delete('pembinas', $id), 404);

        return redirect()->route('admin.dashboard', ['tab' => 'pembina'])->with('success', 'Data Pembina berhasil dihapus!');
    }

    public function storePimpinan(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'posisi' => 'required|in:Ketua Himpunan,Wakil Ketua Himpunan',
            'prodi' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->create('pimpinans', [
            'nama' => $validated['nama'],
            'posisi' => $validated['posisi'],
            'prodi' => $validated['prodi'] ?? null,
            'image_url' => $this->uploadFoto($request, 'pimpinan'),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'pimpinan'])->with('success', 'Data Pimpinan berhasil ditambahkan!');
    }

    public function updatePimpinan(Request $request, int $id)
    {
        $existing = $this->data->find('pimpinans', $id);
        abort_if($existing === null, 404);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'posisi' => 'required|in:Ketua Himpunan,Wakil Ketua Himpunan',
            'prodi' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->update('pimpinans', $id, [
            'nama' => $validated['nama'],
            'posisi' => $validated['posisi'],
            'prodi' => $validated['prodi'] ?? null,
            'image_url' => $this->uploadFoto($request, 'pimpinan') ?? $existing->image_url,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'pimpinan'])->with('success', 'Data Pimpinan berhasil diperbarui!');
    }

    public function destroyPimpinan(int $id)
    {
        abort_unless($this->data->delete('pimpinans', $id), 404);

        return redirect()->route('admin.dashboard', ['tab' => 'pimpinan'])->with('success', 'Data Pimpinan berhasil dihapus!');
    }

    public function storeAnggota(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->data->find('units', $value)) {
                        $fail('Biro atau divisi yang dipilih tidak ditemukan.');
                    }
                },
            ],
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'prodi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->create('anggotas', [
            'unit_id' => (int) $validated['unit_id'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'prodi' => $validated['prodi'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'image_url' => $this->uploadFoto($request, 'anggota') ?? $request->input('image_url'),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'anggota'])->with('success', 'Anggota berhasil ditambahkan!');
    }

    public function updateAnggota(Request $request, int $id)
    {
        $existing = $this->data->find('anggotas', $id);
        abort_if($existing === null, 404);

        $validated = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->data->find('units', $value)) {
                        $fail('Biro atau divisi yang dipilih tidak ditemukan.');
                    }
                },
            ],
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'prodi' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->data->update('anggotas', $id, [
            'unit_id' => (int) $validated['unit_id'],
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'prodi' => $validated['prodi'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'image_url' => $this->uploadFoto($request, 'anggota') ?? $existing->image_url,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'anggota'])->with('success', 'Data anggota berhasil diperbarui!');
    }

    public function destroyAnggota(int $id)
    {
        abort_unless($this->data->delete('anggotas', $id), 404);

        return redirect()->route('admin.dashboard', ['tab' => 'anggota'])->with('success', 'Data anggota berhasil dihapus!');
    }

    private function unitsWithMembers(string $category): Collection
    {
        return $this->data->all('units')
            ->where('kategori', $category)
            ->map(function (object $unit) {
                $unit->anggotas = $this->membersForUnit($unit->id);

                return $unit;
            })
            ->values();
    }

    private function membersForUnit(int|string $unitId): Collection
    {
        return $this->data->all('anggotas')
            ->where('unit_id', (int) $unitId)
            ->values();
    }

    private function publications(): Collection
    {
        return $this->data->all('publications')
            ->sortByDesc(fn (object $publication) => ($publication->tanggal ?? '').' '.($publication->created_at ?? ''))
            ->map(function (object $publication) {
                if ($publication->tanggal ?? null) {
                    $publication->tanggal = Carbon::parse($publication->tanggal);
                }

                return $publication;
            })
            ->values();
    }
}

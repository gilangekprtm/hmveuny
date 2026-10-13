<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C-Panel HMVE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="flex min-h-screen flex-col overflow-x-hidden bg-black text-gray-200 antialiased md:h-screen md:flex-row md:overflow-hidden">

    <!-- Sidebar Berbasis Tab -->
    <aside class="flex w-full shrink-0 flex-col border-b border-yellow-500/20 bg-zinc-900 md:h-full md:w-64 md:border-b-0 md:border-r">
        <div class="flex h-14 shrink-0 items-center justify-center border-b border-yellow-500/20 sm:h-16">
            <h1 class="text-lg font-bold tracking-widest text-white sm:text-xl">C-PANEL HMVE</h1>
        </div>
        <nav class="flex shrink-0 gap-2 overflow-x-auto px-3 py-3 md:flex-1 md:flex-col md:gap-0 md:space-y-2 md:overflow-visible md:px-4 md:py-6">
            <a href="{{ route('admin.dashboard', ['tab' => 'unit']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'unit' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-layer-group"></i>
                <span>Biro & Divisi</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'pembina']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'pembina' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-user-tie"></i>
                <span>Pembina</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'pimpinan']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'pimpinan' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-users-gear"></i>
                <span>Ketua & Wakil</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'anggota']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'anggota' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-users"></i>
                <span>Anggota Biro/Divisi</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'konten']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'konten' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-file-lines"></i>
                <span>Konten Website</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'publikasi']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'publikasi' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-newspaper"></i>
                <span>Publikasi</span>
            </a>
            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="flex shrink-0 items-center space-x-3 whitespace-nowrap rounded-lg px-4 py-3 transition md:shrink md:whitespace-normal {{ $tab == 'users' ? 'bg-yellow-500 text-black font-bold' : 'hover:bg-zinc-800 text-gray-400 hover:text-white' }}">
                <i class="fa-solid fa-user-shield"></i>
                <span>Pengguna</span>
            </a>
        </nav>
        <div class="shrink-0 border-t border-yellow-500/20 p-3 sm:p-4">
            <a href="{{ route('home') }}" class="block w-full text-center border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-black transition">
                <i class="fa-solid fa-globe mr-2"></i> Lihat Website
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto md:h-screen">
        <header class="sticky top-0 z-10 flex min-h-16 flex-wrap items-center justify-between gap-3 border-b border-yellow-500/20 bg-zinc-900/50 px-4 py-3 backdrop-blur sm:px-8 sm:py-0">
            <h2 class="text-base font-semibold uppercase tracking-wider text-white sm:text-xl">
                Manajemen {{ ucfirst($tab) }}
            </h2>
            <div class="flex items-center gap-3 sm:gap-5">
                <div class="text-xs text-gray-400 sm:text-sm">Admin HMVE <i class="fa-solid fa-user-circle ml-1 text-yellow-500 sm:ml-2 sm:text-lg"></i></div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 transition hover:text-yellow-500">Keluar</button>
                </form>
            </div>
        </header>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- KONDISI TAB 1: BIRO & DIVISI -->
            @if($tab == 'unit')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg h-fit">
                    <h3 class="text-lg font-bold text-yellow-500 mb-4 border-b border-zinc-800 pb-2">{{ $editingUnit ? 'Edit Biro / Divisi' : 'Tambah Biro / Divisi' }}</h3>
                    <form action="{{ $editingUnit ? route('admin.unit.update', $editingUnit->id) : route('admin.unit.store') }}" method="POST" enctype="multipart/form-data" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                        @csrf
                        @if($editingUnit)
                        @method('PUT')
                        <p class="mb-4 text-sm font-semibold text-white">{{ $editingUnit->nama }} <span class="text-yellow-500">({{ $editingUnit->kategori }})</span></p>
                        <div class="mb-4">
                            <label for="unit_deskripsi" class="block text-sm font-medium text-gray-400 mb-2">Deskripsi</label>
                            <textarea id="unit_deskripsi" name="deskripsi" required rows="4" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('deskripsi', $editingUnit->deskripsi) }}</textarea>
                        </div>
                        @if($editingUnit->foto_bersama)
                        <img src="{{ asset($editingUnit->foto_bersama) }}" alt="Foto bersama {{ $editingUnit->nama }}" class="mb-4 aspect-video w-full object-cover">
                        @endif
                        <div class="mb-4">
                            <label for="unit_foto" class="block text-sm font-medium text-gray-400 mb-2">{{ $editingUnit->foto_bersama ? 'Ganti foto bersama' : 'Upload foto bersama' }}</label>
                            <input id="unit_foto" type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="unit" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">Simpan Perubahan</button>
                        <a href="{{ route('admin.dashboard', ['tab' => 'unit']) }}" class="block text-center text-sm text-gray-400 hover:text-white mt-3">Batal edit</a>
                        @else
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Nama</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Contoh: Divisi MIKAT">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Kategori</label>
                            <select name="kategori" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                                <option value="Divisi" @selected(old('kategori', 'Divisi') === 'Divisi')>Divisi</option>
                                <option value="Biro" @selected(old('kategori') === 'Biro')>Biro</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Deskripsi</label>
                            <textarea name="deskripsi" required rows="3" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Tugas utama...">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="unit_foto_bersama" class="block text-sm font-medium text-gray-400 mb-2">Foto bersama (opsional)</label>
                            <input id="unit_foto_bersama" type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="unit" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">Simpan Biro/Divisi</button>
                        @endif
                    </form>
                </div>
                <div class="lg:col-span-2 bg-zinc-900 border border-zinc-800 rounded-lg overflow-x-auto h-fit">
                    <div class="p-6 border-b border-zinc-800"><h3 class="text-lg font-bold text-white">Daftar Biro & Divisi</h3></div>
                    <table class="w-full min-w-[36rem] text-left border-collapse">
                        <thead>
                            <tr class="bg-black/40 text-yellow-500 text-sm uppercase">
                                <th class="p-4 border-b border-zinc-800">Nama</th>
                                <th class="p-4 border-b border-zinc-800">Kategori</th>
                                <th class="p-4 border-b border-zinc-800 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $u)
                            <tr class="hover:bg-zinc-800/40 transition">
                                <td class="p-4 border-b border-zinc-800 font-medium text-white">{{ $u->nama }}</td>
                                <td class="p-4 border-b border-zinc-800"><span class="bg-zinc-800 text-yellow-500 px-2 py-1 text-xs font-bold rounded">{{ $u->kategori }}</span></td>
                                <td class="p-4 border-b border-zinc-800 text-center">
                                    <div class="flex items-center justify-center gap-4">
                                        <a href="{{ route('admin.dashboard', ['tab' => 'unit', 'edit_unit' => $u->id]) }}" aria-label="Edit {{ $u->nama }}" class="text-yellow-500 hover:text-yellow-300"><i class="fa-solid fa-pen"></i></a>
                                        <form action="{{ route('admin.unit.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus data?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" aria-label="Hapus {{ $u->nama }}" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-6 text-center text-gray-500">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KONDISI TAB 2: PEMBINA -->
            @elseif($tab == 'pembina')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg h-fit">
                    <h3 class="text-lg font-bold text-yellow-500 mb-4 border-b border-zinc-800 pb-2">Tambah Pembina</h3>
                    <form action="{{ route('admin.pembina.store') }}" method="POST" enctype="multipart/form-data" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Contoh: Dr. Ir. Nama, M.T.">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Jabatan</label>
                            <input type="text" name="jabatan" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Contoh: Pembina Utama HMVE">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Upload Foto (Format PNG Transparan)</label>
                            <input type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="pembina" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">Simpan Pembina</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-zinc-900 border border-zinc-800 rounded-lg overflow-x-auto h-fit">
                    <div class="p-6 border-b border-zinc-800"><h3 class="text-lg font-bold text-white">Daftar Pembina</h3></div>
                    <table class="w-full min-w-[36rem] text-left border-collapse">
                        <thead>
                            <tr class="bg-black/40 text-yellow-500 text-sm uppercase">
                                <th class="p-4 border-b border-zinc-800">Nama</th>
                                <th class="p-4 border-b border-zinc-800">Jabatan</th>
                                <th class="p-4 border-b border-zinc-800 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembinas as $p)
                            <tr class="hover:bg-zinc-800/40 transition">
                                <td class="p-4 border-b border-zinc-800 font-medium text-white">{{ $p->nama }}</td>
                                <td class="p-4 border-b border-zinc-800 text-gray-400 text-sm">{{ $p->jabatan }}</td>
                                <td class="p-4 border-b border-zinc-800 text-center">
                                    <form action="{{ route('admin.pembina.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-6 text-center text-gray-500">Belum ada data pembina.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KONDISI TAB 3: KETUA & WAKIL -->
            @elseif($tab == 'pimpinan')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg h-fit">
                    <h3 class="text-lg font-bold text-yellow-500 mb-4 border-b border-zinc-800 pb-2">Tambah Ketua / Wakil</h3>
                    <form action="{{ route('admin.pimpinan.store') }}" method="POST" enctype="multipart/form-data" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Nama Mahasiswa">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Posisi</label>
                            <select name="posisi" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                                <option value="Ketua Himpunan">Ketua Himpunan</option>
                                <option value="Wakil Ketua Himpunan">Wakil Ketua Himpunan</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Upload Foto (Format PNG Transparan)</label>
                            <input type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="pimpinan" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">Simpan Pimpinan</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-zinc-900 border border-zinc-800 rounded-lg overflow-x-auto h-fit">
                    <div class="p-6 border-b border-zinc-800"><h3 class="text-lg font-bold text-white">Daftar Ketua & Wakil Ketua</h3></div>
                    <table class="w-full min-w-[36rem] text-left border-collapse">
                        <thead>
                            <tr class="bg-black/40 text-yellow-500 text-sm uppercase">
                                <th class="p-4 border-b border-zinc-800">Nama</th>
                                <th class="p-4 border-b border-zinc-800">Posisi</th>
                                <th class="p-4 border-b border-zinc-800 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pimpinans as $pim)
                            <tr class="hover:bg-zinc-800/40 transition">
                                <td class="p-4 border-b border-zinc-800 font-medium text-white">{{ $pim->nama }}</td>
                                <td class="p-4 border-b border-zinc-800"><span class="bg-yellow-500/10 text-yellow-500 px-2 py-1 text-xs font-bold rounded border border-yellow-500/20">{{ $pim->posisi }}</span></td>
                                <td class="p-4 border-b border-zinc-800 text-center">
                                    <form action="{{ route('admin.pimpinan.destroy', $pim->id) }}" method="POST" onsubmit="return confirm('Hapus data?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="p-6 text-center text-gray-500">Belum ada data pimpinan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KONDISI TAB 4: ANGGOTA -->
            @elseif($tab == 'anggota')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg h-fit">
                    <h3 class="text-lg font-bold text-yellow-500 mb-4 border-b border-zinc-800 pb-2">Tambah Anggota</h3>
                    <form action="{{ route('admin.anggota.store') }}" method="POST" enctype="multipart/form-data" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Pilih Biro / Divisi</label>
                            <select name="unit_id" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                                <option value="">-- Pilih Penempatan --</option>
                                @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->kategori }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Nama Anggota">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Jabatan</label>
                            <input type="text" name="jabatan" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Contoh: Kepala Divisi / Staff">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Deskripsi Singkat</label>
                            <textarea name="deskripsi" rows="2" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="Keterangan singkat..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">URL Foto (Opsional)</label>
                            <input type="text" name="image_url" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none" placeholder="https://...">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Upload Foto (Format PNG Transparan)</label>
                            <input type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="anggota" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">Simpan Anggota</button>
                    </form>
                </div>
                <div class="lg:col-span-2 bg-zinc-900 border border-zinc-800 rounded-lg overflow-x-auto h-fit">
                    <div class="p-6 border-b border-zinc-800"><h3 class="text-lg font-bold text-white">Daftar Anggota Biro & Divisi</h3></div>
                    <table class="w-full min-w-[36rem] text-left border-collapse">
                        <thead>
                            <tr class="bg-black/40 text-yellow-500 text-sm uppercase">
                                <th class="p-4 border-b border-zinc-800">Nama</th>
                                <th class="p-4 border-b border-zinc-800">Penempatan</th>
                                <th class="p-4 border-b border-zinc-800">Jabatan</th>
                                <th class="p-4 border-b border-zinc-800 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($anggotas as $ang)
                            <tr class="hover:bg-zinc-800/40 transition">
                                <td class="p-4 border-b border-zinc-800 font-medium text-white">{{ $ang->nama }}</td>
                                <td class="p-4 border-b border-zinc-800 text-yellow-500 text-sm">{{ $ang->unit->nama ?? '-' }}</td>
                                <td class="p-4 border-b border-zinc-800 text-gray-400 text-sm">{{ $ang->jabatan }}</td>
                                <td class="p-4 border-b border-zinc-800 text-center">
                                    <form action="{{ route('admin.anggota.destroy', $ang->id) }}" method="POST" onsubmit="return confirm('Hapus anggota ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada data anggota.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif($tab == 'konten')
            <form action="{{ route('admin.content.update') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl space-y-8" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                @csrf
                @method('PUT')
                <section class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg">
                    <h3 class="text-lg font-bold text-yellow-500 mb-5 border-b border-zinc-800 pb-2">Profil HMVE</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="sejarah" class="block text-sm font-medium text-gray-400 mb-2">Sejarah singkat</label>
                            <textarea id="sejarah" name="sejarah" rows="4" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('sejarah', $siteContent->sejarah) }}</textarea>
                        </div>
                        <div>
                            <label for="visi" class="block text-sm font-medium text-gray-400 mb-2">Visi</label>
                            <textarea id="visi" name="visi" rows="4" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('visi', $siteContent->visi) }}</textarea>
                        </div>
                        <div>
                            <label for="misi" class="block text-sm font-medium text-gray-400 mb-2">Misi</label>
                            <textarea id="misi" name="misi" rows="4" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('misi', $siteContent->misi) }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg">
                    <h3 class="text-lg font-bold text-yellow-500 mb-5 border-b border-zinc-800 pb-2">Foto Bersama di Hero</h3>
                    <p class="mb-4 text-sm text-gray-400">Foto ini ditampilkan sebagai latar belakang hero di halaman utama.</p>
                    @if($siteContent->hero_image)
                    <img src="{{ asset($siteContent->hero_image) }}" alt="Pratinjau foto bersama anggota HMVE" class="mb-5 aspect-video max-h-80 w-full rounded border border-zinc-800 object-cover">
                    @endif
                    <label for="hero_foto" class="mb-2 block text-sm font-medium text-gray-400">{{ $siteContent->hero_image ? 'Ganti foto bersama' : 'Unggah foto bersama' }}</label>
                    <input id="hero_foto" type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="hero" class="w-full text-sm text-gray-400 file:mr-4 file:rounded file:border-0 file:bg-yellow-500 file:px-4 file:py-2 file:font-semibold file:text-black hover:file:bg-yellow-400">
                    <p class="mt-2 text-xs text-gray-500">Format PNG, JPG, atau WebP. Ukuran maksimal 10 MB.</p>
                    @if($siteContent->hero_image)
                    <label for="remove_hero_image" class="mt-5 flex cursor-pointer items-center gap-3 border-t border-zinc-800 pt-4">
                        <input id="remove_hero_image" type="checkbox" name="remove_hero_image" value="1" class="h-4 w-4 accent-red-500">
                        <span class="text-sm text-red-300">Hapus foto hero saat menyimpan</span>
                    </label>
                    @endif
                </section>

                <section class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg">
                    <h3 class="text-lg font-bold text-yellow-500 mb-5 border-b border-zinc-800 pb-2">Pendaftaran Anggota</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="join_title" class="block text-sm font-medium text-gray-400 mb-2">Judul ajakan</label>
                            <input id="join_title" type="text" name="join_title" value="{{ old('join_title', $siteContent->join_title) }}" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="join_label" class="block text-sm font-medium text-gray-400 mb-2">Teks tombol</label>
                            <input id="join_label" type="text" name="join_label" value="{{ old('join_label', $siteContent->join_label) }}" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="join_url" class="block text-sm font-medium text-gray-400 mb-2">URL formulir pendaftaran</label>
                            <input id="join_url" type="url" name="join_url" value="{{ old('join_url', $siteContent->join_url) }}" placeholder="https://..." class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="join_description" class="block text-sm font-medium text-gray-400 mb-2">Deskripsi ajakan</label>
                            <textarea id="join_description" name="join_description" rows="3" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('join_description', $siteContent->join_description) }}</textarea>
                        </div>
                        <div class="md:col-span-2 border-t border-zinc-800 pt-5">
                            <input type="hidden" name="show_join_menu" value="0">
                            <label for="show_join_menu" class="flex cursor-pointer items-start gap-3">
                                <input id="show_join_menu" type="checkbox" name="show_join_menu" value="1" @checked(old('show_join_menu', $siteContent->show_join_menu)) class="mt-1 h-4 w-4 accent-yellow-500">
                                <span>
                                    <span class="block text-sm font-semibold text-white">Tampilkan menu Bergabung</span>
                                    <span class="mt-1 block text-sm text-gray-400">Menampilkan tautan di navbar dan tombol pada hero halaman utama.</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg">
                    <h3 class="text-lg font-bold text-yellow-500 mb-5 border-b border-zinc-800 pb-2">Kontak & Media Sosial</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-400 mb-2">Email</label>
                            <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $siteContent->contact_email) }}" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-400 mb-2">Nomor telepon / WhatsApp</label>
                            <input id="contact_phone" type="text" name="contact_phone" value="{{ old('contact_phone', $siteContent->contact_phone) }}" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label for="contact_address" class="block text-sm font-medium text-gray-400 mb-2">Alamat sekretariat</label>
                            <textarea id="contact_address" name="contact_address" rows="2" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('contact_address', $siteContent->contact_address) }}</textarea>
                        </div>
                        <div>
                            <label for="instagram_url" class="block text-sm font-medium text-gray-400 mb-2">URL Instagram</label>
                            <input id="instagram_url" type="url" name="instagram_url" value="{{ old('instagram_url', $siteContent->instagram_url) }}" placeholder="https://instagram.com/..." class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="youtube_url" class="block text-sm font-medium text-gray-400 mb-2">URL YouTube</label>
                            <input id="youtube_url" type="url" name="youtube_url" value="{{ old('youtube_url', $siteContent->youtube_url) }}" placeholder="https://youtube.com/..." class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div>
                            <label for="tiktok_url" class="block text-sm font-medium text-gray-400 mb-2">URL TikTok</label>
                            <input id="tiktok_url" type="url" name="tiktok_url" value="{{ old('tiktok_url', $siteContent->tiktok_url) }}" placeholder="https://tiktok.com/@..." class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                    </div>
                </section>

                <button type="submit" class="bg-yellow-500 text-black font-bold px-6 py-3 rounded hover:bg-yellow-400 transition">Simpan Konten Website</button>
            </form>
            @elseif($tab == 'users')
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <section class="h-fit rounded-lg border border-zinc-800 bg-zinc-900 p-6">
                    <h3 class="mb-4 border-b border-zinc-800 pb-2 text-lg font-bold text-yellow-500">
                        {{ $editingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}
                    </h3>
                    <form action="{{ $editingUser ? route('admin.users.update', $editingUser->id) : route('admin.users.store') }}" method="POST" class="space-y-4">
                        @csrf
                        @if($editingUser)
                        @method('PUT')
                        @endif
                        <div>
                            <label for="user_name" class="mb-2 block text-sm font-medium text-gray-400">Nama</label>
                            <input id="user_name" type="text" name="name" value="{{ old('name', $editingUser?->name) }}" required maxlength="255" class="w-full rounded border border-zinc-700 bg-black px-4 py-2 text-white outline-none focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="user_email" class="mb-2 block text-sm font-medium text-gray-400">Email</label>
                            <input id="user_email" type="email" name="email" value="{{ old('email', $editingUser?->email) }}" required maxlength="255" class="w-full rounded border border-zinc-700 bg-black px-4 py-2 text-white outline-none focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="user_password" class="mb-2 block text-sm font-medium text-gray-400">Password {{ $editingUser ? '(opsional, kosongkan jika tidak diganti)' : '' }}</label>
                            <input id="user_password" type="password" name="password" {{ $editingUser ? '' : 'required' }} minlength="8" autocomplete="new-password" class="w-full rounded border border-zinc-700 bg-black px-4 py-2 text-white outline-none focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="user_password_confirmation" class="mb-2 block text-sm font-medium text-gray-400">Konfirmasi password {{ $editingUser ? '(opsional)' : '' }}</label>
                            <input id="user_password_confirmation" type="password" name="password_confirmation" {{ $editingUser ? '' : 'required' }} minlength="8" autocomplete="new-password" class="w-full rounded border border-zinc-700 bg-black px-4 py-2 text-white outline-none focus:border-yellow-500">
                        </div>
                        <div>
                            <label for="user_level_akses" class="mb-2 block text-sm font-medium text-gray-400">Level akses</label>
                            @if($editingUser && (int) $editingUser->id === $currentUserId)
                            <input type="hidden" name="level_akses" value="2">
                            @endif
                            <select id="user_level_akses" name="{{ $editingUser && (int) $editingUser->id === $currentUserId ? '' : 'level_akses' }}" {{ $editingUser && (int) $editingUser->id === $currentUserId ? 'disabled' : '' }} required class="w-full rounded border border-zinc-700 bg-black px-4 py-2 text-white outline-none focus:border-yellow-500 disabled:opacity-60">
                                <option value="1" @selected((string) old('level_akses', $editingUser?->level_akses ?? 1) === '1')>1 - Pengguna</option>
                                <option value="2" @selected((string) old('level_akses', $editingUser?->level_akses ?? 1) === '2')>2 - Admin</option>
                            </select>
                            @if($editingUser && (int) $editingUser->id === $currentUserId)
                            <p class="mt-2 text-xs text-gray-500">Level akun yang sedang digunakan tidak dapat diubah.</p>
                            @endif
                        </div>
                        <button type="submit" class="w-full rounded bg-yellow-500 py-2 font-bold text-black transition hover:bg-yellow-400">
                            {{ $editingUser ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
                        </button>
                        @if($editingUser)
                        <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="block text-center text-sm text-gray-400 hover:text-white">Batal edit</a>
                        @endif
                    </form>
                </section>

                <section class="h-fit overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900 xl:col-span-2">
                    <div class="border-b border-zinc-800 p-6">
                        <h3 class="text-lg font-bold text-white">Daftar Pengguna</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[40rem] border-collapse text-left">
                            <thead>
                                <tr class="bg-black/40 text-sm uppercase text-yellow-500">
                                    <th class="border-b border-zinc-800 p-4">Nama</th>
                                    <th class="border-b border-zinc-800 p-4">Email</th>
                                    <th class="border-b border-zinc-800 p-4">Level</th>
                                    <th class="border-b border-zinc-800 p-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $managedUser)
                                <tr class="transition hover:bg-zinc-800/40">
                                    <td class="border-b border-zinc-800 p-4 font-medium text-white">
                                        {{ $managedUser->name }}
                                        @if((int) $managedUser->id === $currentUserId)
                                        <span class="ml-1 text-xs text-yellow-500">(Anda)</span>
                                        @endif
                                    </td>
                                    <td class="border-b border-zinc-800 p-4 text-sm text-gray-400">{{ $managedUser->email }}</td>
                                    <td class="border-b border-zinc-800 p-4 text-sm text-gray-400">
                                        <span class="rounded bg-zinc-800 px-2 py-1 {{ $managedUser->level_akses === 2 ? 'text-yellow-500' : 'text-gray-300' }}">
                                            {{ $managedUser->level_akses === 2 ? 'Admin (2)' : 'Pengguna (1)' }}
                                        </span>
                                    </td>
                                    <td class="border-b border-zinc-800 p-4">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="{{ route('admin.dashboard', ['tab' => 'users', 'edit_user' => $managedUser->id]) }}" aria-label="Edit pengguna {{ $managedUser->name }}" class="text-yellow-500 hover:text-yellow-300"><i class="fa-solid fa-pen"></i></a>
                                            @if((int) $managedUser->id !== $currentUserId)
                                            <form action="{{ route('admin.users.destroy', $managedUser->id) }}" method="POST" onsubmit="return confirm('Hapus akun pengguna ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Hapus pengguna {{ $managedUser->name }}" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada pengguna.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            @elseif($tab == 'publikasi')
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <section class="bg-zinc-900 border border-zinc-800 p-6 rounded-lg h-fit">
                    <h3 class="text-lg font-bold text-yellow-500 mb-4 border-b border-zinc-800 pb-2">{{ $editingPublication ? 'Edit Publikasi' : 'Tambah Publikasi' }}</h3>
                    <form action="{{ $editingPublication ? route('admin.publication.update', $editingPublication->id) : route('admin.publication.store') }}" method="POST" enctype="multipart/form-data" data-blob-upload="{{ config('uploads.driver') === 'vercel' ? 'true' : 'false' }}">
                        @csrf
                        @if($editingPublication)
                        @method('PUT')
                        @endif
                        <div class="mb-4">
                            <label for="kategori" class="block text-sm font-medium text-gray-400 mb-2">Kategori</label>
                            <select id="kategori" name="kategori" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                                @foreach(['Berita', 'Kegiatan', 'Program Kerja', 'Karya & Prestasi'] as $category)
                                <option value="{{ $category }}" @selected(old('kategori', $editingPublication?->kategori) === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="judul" class="block text-sm font-medium text-gray-400 mb-2">Judul</label>
                            <input id="judul" type="text" name="judul" value="{{ old('judul', $editingPublication?->judul) }}" required class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div class="mb-4">
                            <label for="tanggal" class="block text-sm font-medium text-gray-400 mb-2">Tanggal</label>
                            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', $editingPublication?->tanggal?->format('Y-m-d')) }}" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">
                        </div>
                        <div class="mb-4">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-400 mb-2">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" required rows="6" class="w-full bg-black border border-zinc-700 rounded px-4 py-2 text-white focus:border-yellow-500 outline-none">{{ old('deskripsi', $editingPublication?->deskripsi) }}</textarea>
                        </div>
                        @if($editingPublication?->image_url)
                        <img src="{{ asset($editingPublication->image_url) }}" alt="" class="w-full aspect-video object-cover mb-4">
                        @endif
                        <div class="mb-4">
                            <label for="foto" class="block text-sm font-medium text-gray-400 mb-2">{{ $editingPublication?->image_url ? 'Ganti gambar (opsional)' : 'Gambar (opsional)' }}</label>
                            <input id="foto" type="file" name="foto" accept="image/png, image/jpeg, image/webp" data-blob-folder="publikasi" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-500 file:text-black hover:file:bg-yellow-400">
                        </div>
                        <button type="submit" class="w-full bg-yellow-500 text-black font-bold py-2 rounded hover:bg-yellow-400 transition">{{ $editingPublication ? 'Simpan Perubahan' : 'Simpan Publikasi' }}</button>
                        @if($editingPublication)
                        <a href="{{ route('admin.dashboard', ['tab' => 'publikasi']) }}" class="block text-center text-sm text-gray-400 hover:text-white mt-3">Batal edit</a>
                        @endif
                    </form>
                </section>

                <section class="xl:col-span-2 bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden h-fit">
                    <div class="p-6 border-b border-zinc-800"><h3 class="text-lg font-bold text-white">Daftar Publikasi</h3></div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[36rem] text-left border-collapse">
                            <thead>
                                <tr class="bg-black/40 text-yellow-500 text-sm uppercase">
                                    <th class="p-4 border-b border-zinc-800">Judul</th>
                                    <th class="p-4 border-b border-zinc-800">Kategori</th>
                                    <th class="p-4 border-b border-zinc-800">Tanggal</th>
                                    <th class="p-4 border-b border-zinc-800 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($publications as $publication)
                                <tr class="hover:bg-zinc-800/40 transition">
                                    <td class="p-4 border-b border-zinc-800 font-medium text-white">{{ $publication->judul }}</td>
                                    <td class="p-4 border-b border-zinc-800 text-sm text-gray-400">{{ $publication->kategori }}</td>
                                    <td class="p-4 border-b border-zinc-800 text-sm text-gray-400">{{ $publication->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="p-4 border-b border-zinc-800">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="{{ route('admin.dashboard', ['tab' => 'publikasi', 'edit' => $publication->id]) }}" aria-label="Edit {{ $publication->judul }}" class="text-yellow-500 hover:text-yellow-300"><i class="fa-solid fa-pen"></i></a>
                                            <form action="{{ route('admin.publication.destroy', $publication->id) }}" method="POST" onsubmit="return confirm('Hapus publikasi ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" aria-label="Hapus {{ $publication->judul }}" class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada publikasi.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            @endif

        </div>
    </main>

    @include('components.toast-notifications')
</body>
</html>
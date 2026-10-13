<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMVE - Himpunan Mahasiswa Vokasi Elektro-Elektronika</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="top" class="bg-black text-gray-200 antialiased font-sans">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-black/90 backdrop-blur-md border-b border-yellow-500/30">
        <div class="container mx-auto flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4">
            <a href="{{ url('/') }}" aria-label="HMVE Beranda" class="flex shrink-0 items-center gap-2 sm:gap-3">
                <img src="{{ asset('images/hmve.jpg') }}" alt="Logo HMVE" class="h-9 w-9 object-contain sm:h-10 sm:w-10">
                <h1 class="text-xl font-bold tracking-widest text-white sm:text-2xl">HMVE</h1>
            </a>
            <ul class="hidden space-x-6 text-sm font-semibold uppercase tracking-widest lg:flex xl:space-x-8">
                <li><a href="#" class="hover:text-yellow-500 transition">Beranda</a></li>
                <li>
                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 hover:text-yellow-500 transition">
                            Struktur <span aria-hidden="true" class="mt-[-0.2rem] h-2 w-2 rotate-45 border-b-2 border-r-2 border-yellow-500 transition-transform group-open:mt-1 group-open:rotate-[225deg]"></span>
                        </summary>
                        <div data-menu-panel class="absolute left-0 top-full z-50 mt-3 max-h-[70vh] w-64 overflow-y-auto border border-zinc-800 bg-black p-4 shadow-xl">
                            <a href="#struktur" class="mb-4 block border-b border-zinc-800 pb-3 text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-yellow-500">Ringkasan Struktur</a>
                            @if($biros->isNotEmpty())
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-yellow-500">Biro</h3>
                            <ul class="mb-4 space-y-2">
                                @foreach($biros as $biro)
                                <li><a href="{{ route('unit.detail', $biro->slug) }}" class="block normal-case tracking-normal text-gray-300 transition hover:text-yellow-500">{{ $biro->nama }}</a></li>
                                @endforeach
                            </ul>
                            @endif
                            @if($divisis->isNotEmpty())
                            <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-yellow-500">Divisi</h3>
                            <ul class="space-y-2">
                                @foreach($divisis as $divisi)
                                <li><a href="{{ route('unit.detail', $divisi->slug) }}" class="block normal-case tracking-normal text-gray-300 transition hover:text-yellow-500">{{ $divisi->nama }}</a></li>
                                @endforeach
                            </ul>
                            @endif
                            @if($biros->isEmpty() && $divisis->isEmpty())
                            <p class="text-sm normal-case tracking-normal text-gray-500">Belum ada Biro atau Divisi.</p>
                            @endif
                        </div>
                    </details>
                </li>
                @if($siteContent?->show_join_menu)
                <li class="hidden sm:block"><a href="{{ route('join') }}" class="hover:text-yellow-500 transition">Bergabung</a></li>
                @endif
                @if($siteContent && ($siteContent->sejarah || $siteContent->visi || $siteContent->misi))
                <li class="hidden lg:block"><a href="#tentang" class="hover:text-yellow-500 transition">Tentang</a></li>
                @endif
                @if($publications->isNotEmpty())
                <li class="hidden lg:block"><a href="#publikasi" class="hover:text-yellow-500 transition">Kegiatan</a></li>
                @endif
                @if($siteContent && ($siteContent->contact_email || $siteContent->contact_phone || $siteContent->contact_address || $siteContent->instagram_url || $siteContent->youtube_url || $siteContent->tiktok_url))
                <li class="hidden lg:block"><a href="#kontak" class="hover:text-yellow-500 transition">Kontak</a></li>
                @endif
            </ul>
            <details class="group relative lg:hidden">
                <summary class="flex cursor-pointer list-none items-center gap-2 py-2 text-sm font-semibold uppercase tracking-widest text-white hover:text-yellow-500">
                    Menu
                    <span aria-hidden="true" class="flex w-4 flex-col gap-1">
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                    </span>
                </summary>
                <div data-menu-panel class="absolute right-0 top-full z-50 mt-3 max-h-[75vh] w-[min(90vw,22rem)] overflow-y-auto border border-zinc-800 bg-black p-3 shadow-xl">
                    <a href="{{ route('home') }}" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Beranda</a>
                    <a href="#struktur" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Struktur Organisasi</a>
                    @if($siteContent?->show_join_menu)
                    <a href="{{ route('join') }}" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Bergabung</a>
                    @endif
                    @if($siteContent && ($siteContent->sejarah || $siteContent->visi || $siteContent->misi))
                    <a href="#tentang" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Profil HMVE</a>
                    @endif
                    @if($publications->isNotEmpty())
                    <a href="#publikasi" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Kabar, Kegiatan & Karya</a>
                    @endif
                    @if($siteContent && ($siteContent->contact_email || $siteContent->contact_phone || $siteContent->contact_address || $siteContent->instagram_url || $siteContent->youtube_url || $siteContent->tiktok_url))
                    <a href="#kontak" class="block px-3 py-2 text-sm text-gray-200 hover:text-yellow-500">Kontak</a>
                    @endif
                    @if($biros->isNotEmpty())
                    <h2 class="mt-3 border-t border-zinc-800 px-3 pt-3 text-xs font-bold uppercase tracking-widest text-yellow-500">Biro</h2>
                    @foreach($biros as $biro)
                    <a href="{{ route('unit.detail', $biro->slug) }}" class="block px-3 py-2 text-sm text-gray-300 hover:text-yellow-500">{{ $biro->nama }}</a>
                    @endforeach
                    @endif
                    @if($divisis->isNotEmpty())
                    <h2 class="mt-3 border-t border-zinc-800 px-3 pt-3 text-xs font-bold uppercase tracking-widest text-yellow-500">Divisi</h2>
                    @foreach($divisis as $divisi)
                    <a href="{{ route('unit.detail', $divisi->slug) }}" class="block px-3 py-2 text-sm text-gray-300 hover:text-yellow-500">{{ $divisi->nama }}</a>
                    @endforeach
                    @endif
                </div>
            </details>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative w-full min-h-svh flex items-end pb-20 justify-center bg-zinc-950 border-b border-zinc-900">
        @if($siteContent?->hero_image)
        <img src="{{ asset($siteContent->hero_image) }}" alt="Foto bersama anggota HMVE" class="absolute inset-0 h-full w-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
        <div class="hero-copy relative z-10 mx-auto w-full px-4 text-center">
            <h2 class="w-full text-center font-extrabold leading-tight tracking-tight">
                <span class="block whitespace-nowrap text-[clamp(1rem,6vw,3.75rem)] text-white">HIMPUNAN MAHASISWA VOKASI</span>
                <span class="mt-2 block text-[clamp(1.5rem,7vw,3.75rem)] text-yellow-500">ELEKTRO-ELEKTRONIKA</span>
            </h2>
            <p class="mt-3 text-sm font-medium text-white sm:text-base md:text-lg">Universitas Negeri Yogyakarta</p>
            <div class="w-20 h-1 bg-yellow-500 mx-auto mt-4"></div>
            @if($siteContent?->show_join_menu)
            <a href="{{ route('join') }}" class="inline-block mt-8 border border-yellow-500 px-6 py-3 text-sm font-bold uppercase tracking-widest text-yellow-500 transition hover:bg-yellow-500 hover:text-black">Bergabung dengan HMVE</a>
            @endif
        </div>
    </section>

    <!-- Konten Utama -->
    <div class="container mx-auto space-y-16 px-4 py-12 sm:px-6 sm:py-16 md:space-y-24">

        @if($siteContent && ($siteContent->sejarah || $siteContent->visi || $siteContent->misi))
        <section id="tentang" data-reveal>
            <h3 class="text-center text-2xl font-bold text-white uppercase">Profil HMVE</h3>
            <div class="mx-auto mt-3 mb-8 h-1 w-16 bg-yellow-500"></div>
            @if($siteContent->sejarah)
            <p class="mx-auto max-w-4xl text-center leading-relaxed text-gray-300">{{ $siteContent->sejarah }}</p>
            @endif
            @if($siteContent->visi || $siteContent->misi)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8" data-reveal-group>
                @if($siteContent->visi)
                <div class="border-t border-yellow-500/50 pt-4 text-center" data-reveal>
                    <h4 class="text-lg font-bold text-yellow-500 uppercase mb-3">Visi</h4>
                    <p class="text-gray-300 leading-relaxed whitespace-pre-line">{{ $siteContent->visi }}</p>
                </div>
                @endif
                @if($siteContent->misi)
                <div class="border-t border-yellow-500/50 pt-4 text-center" data-reveal>
                    <h4 class="text-lg font-bold text-yellow-500 uppercase mb-3">Misi</h4>
                    @php
                        $misiItems = preg_split('/\R+/', trim($siteContent->misi));
                    @endphp
                    <ol class="mx-auto max-w-3xl list-decimal space-y-3 pl-6 text-left leading-relaxed text-gray-300">
                        @foreach($misiItems as $misiItem)
                        <li class="pl-2">{{ preg_replace('/^\d+[.)]\s*/', '', $misiItem) }}</li>
                        @endforeach
                    </ol>
                </div>
                @endif
            </div>
            @endif
        </section>
        @endif

        @if($publications->isNotEmpty())
        <section id="publikasi" data-reveal>
            <h3 class="text-center text-2xl font-bold text-white uppercase">Kabar, Kegiatan & Karya</h3>
            <div class="mx-auto mt-3 mb-8 h-1 w-16 bg-yellow-500"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-reveal-group>
                @foreach($publications as $publication)
                <article data-reveal class="border border-zinc-800 bg-zinc-950 overflow-hidden transition-transform duration-500 hover:-translate-y-1">
                    @if($publication->image_url)
                    <img src="{{ asset($publication->image_url) }}" alt="{{ $publication->judul }}" class="w-full aspect-video object-cover">
                    @endif
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-yellow-500">{{ $publication->kategori }}</span>
                            @if($publication->tanggal)
                            <time datetime="{{ $publication->tanggal->format('Y-m-d') }}" class="text-xs text-zinc-500">{{ $publication->tanggal->format('d/m/Y') }}</time>
                            @endif
                        </div>
                        <h4 class="text-lg font-bold text-white mb-2">{{ $publication->judul }}</h4>
                        <p class="text-sm leading-relaxed text-gray-400 whitespace-pre-line line-clamp-4">{{ $publication->deskripsi }}</p>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif
        
        <div id="struktur" class="text-center" data-reveal>
            <h2 class="text-3xl font-bold text-white uppercase">Struktur Organisasi</h2>
            <div class="mx-auto mt-4 h-1 w-20 bg-yellow-500"></div>
        </div>

        <!-- 1. Pembina -->
        <section data-reveal>
            <h3 class="text-2xl font-bold text-white uppercase mb-8 border-l-4 border-yellow-500 pl-3">Pembina</h3>
            <div class="grid grid-cols-1 max-w-xs mx-auto">
                @forelse($pembinas->take(1) as $p)
                <div class="bg-zinc-950 border border-zinc-800 rounded-sm overflow-hidden group">
                    <div class="relative w-full aspect-[4/5] bg-zinc-950 flex items-center justify-center overflow-hidden">
                        @if($p->image_url)
                        <img src="{{ filter_var($p->image_url, FILTER_VALIDATE_URL) ? $p->image_url : asset($p->image_url) }}" alt="{{ $p->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-6xl font-bold text-zinc-700">{{ mb_substr($p->nama, 0, 1) }}</div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60"></div>
                    </div>
                    <div class="p-6 border-t border-zinc-800">
                        <h4 class="text-2xl font-bold text-white mb-1">{{ $p->nama }}</h4>
                        <p class="text-yellow-500 text-sm uppercase tracking-widest font-semibold">{{ $p->jabatan }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center">Belum ada data pembina.</p>
                @endforelse
            </div>
        </section>

        <!-- 2. Ketua & Wakil -->
        <section data-reveal>
            <h3 class="text-2xl font-bold text-white uppercase mb-8 border-l-4 border-yellow-500 pl-3">Ketua & Wakil Himpunan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
                @forelse($pimpinans as $pim)
                <div class="bg-zinc-950 border border-zinc-800 rounded-sm overflow-hidden group">
                    <!-- Area Foto dengan penanganan PNG transparan -->
                    <div class="relative w-full aspect-[4/5] bg-zinc-950 flex items-center justify-center overflow-hidden">
                        <img src="{{ $pim->image_url ? (filter_var($pim->image_url, FILTER_VALIDATE_URL) ? $pim->image_url : asset($pim->image_url)) : asset('images/lang.png') }}" alt="{{ $pim->nama }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60"></div>
                    </div>
                    <!-- Informasi Nama & Jabatan -->
                    <div class="p-6 border-t border-zinc-800">
                        <h4 class="text-2xl font-bold text-white mb-1">{{ $pim->nama }}</h4>
                        <p class="text-yellow-500 text-sm uppercase tracking-widest font-semibold">{{ $pim->posisi }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center col-span-2">Belum ada data pimpinan.</p>
                @endforelse
            </div>
        </section>

        <!-- 3. Biro & 4. Divisi -->
        <section data-reveal>
            <h3 class="text-2xl font-bold text-white uppercase mb-6 border-l-4 border-yellow-500 pl-3">Biro & Divisi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-reveal-group>
                @foreach($biros->concat($divisis) as $item)
                <a data-reveal href="{{ route('unit.detail', $item->slug) }}" class="group border border-zinc-800 bg-zinc-950 p-6 hover:-translate-y-1 hover:border-yellow-500 transition block">
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 font-bold uppercase rounded-sm">{{ $item->kategori }}</span>
                    <h4 class="text-xl font-bold text-white mt-3 group-hover:text-yellow-500 transition">{{ $item->nama }}</h4>
                    <p class="text-gray-400 text-sm mt-2 leading-relaxed">{{ $item->deskripsi }}</p>
                    @if($unitLeaders->get($item->id)?->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-4 border-t border-zinc-800 pt-4">
                        @foreach($unitLeaders->get($item->id) as $leader)
                        <div class="flex items-center gap-3">
                            @if($leader->image_url)
                            <img src="{{ filter_var($leader->image_url, FILTER_VALIDATE_URL) ? $leader->image_url : asset($leader->image_url) }}" alt="{{ $leader->nama }}" class="w-12 h-12 rounded-full object-contain bg-black border border-yellow-500/50 p-1">
                            @else
                            <div class="w-12 h-12 rounded-full flex items-center justify-center bg-black border border-zinc-700 text-yellow-500 font-bold">{{ mb_substr($leader->nama, 0, 1) }}</div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $leader->nama }}</p>
                                <p class="text-xs text-yellow-500">{{ $leader->jabatan }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <span class="text-xs text-zinc-500 group-hover:text-yellow-500 transition uppercase tracking-widest mt-4 block">
                        Lihat Anggota &rarr;
                    </span>
                </a>
                @endforeach
            </div>
        </section>

    </div>

    <!-- Footer -->
    <footer class="mt-12 border-t border-yellow-500/30 bg-black">
        <div class="container mx-auto grid grid-cols-1 gap-10 px-6 py-14 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
            <div data-reveal>
                <a href="{{ route('home') }}" aria-label="HMVE Beranda" class="mb-6 inline-flex items-center gap-3">
                    <img src="{{ asset('images/hmve.jpg') }}" alt="Logo HMVE" class="h-11 w-11 object-contain">
                    <span class="text-xl font-bold tracking-widest text-white">HMVE</span>
                </a>
                <h2 class="mb-3 text-sm font-bold uppercase tracking-widest text-yellow-500">Tentang HMVE</h2>
                <p class="max-w-xs text-sm leading-relaxed text-gray-400">{{ \Illuminate\Support\Str::limit($siteContent?->sejarah ?: 'Himpunan Mahasiswa Vokasi Elektro-Elektronika Universitas Negeri Yogyakarta.', 180) }}</p>
            </div>

            <div data-reveal>
                <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-yellow-500">Informasi</h2>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="{{ route('home') }}" class="transition hover:text-yellow-500">Beranda</a></li>
                    @if($siteContent && ($siteContent->sejarah || $siteContent->visi || $siteContent->misi))
                    <li><a href="{{ route('home') }}#tentang" class="transition hover:text-yellow-500">Profil HMVE</a></li>
                    @endif
                    @if($publications->isNotEmpty())
                    <li><a href="{{ route('home') }}#publikasi" class="transition hover:text-yellow-500">Kabar & Kegiatan</a></li>
                    @endif
                    <li><a href="{{ route('home') }}#struktur" class="transition hover:text-yellow-500">Struktur Organisasi</a></li>
                </ul>
            </div>

            <div data-reveal>
                <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-yellow-500">Tautan HMVE</h2>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="{{ route('home') }}#struktur" class="transition hover:text-yellow-500">Pembina & Pimpinan</a></li>
                    <li><a href="{{ route('home') }}#struktur" class="transition hover:text-yellow-500">Biro & Divisi</a></li>
                    @if($siteContent?->show_join_menu)
                    <li><a href="{{ route('join') }}" class="transition hover:text-yellow-500">Bergabung dengan HMVE</a></li>
                    @endif
                </ul>
            </div>

            <div id="kontak" class="scroll-mt-24" data-reveal>
                <h2 class="mb-5 text-sm font-bold uppercase tracking-widest text-yellow-500">Hubungi Kami</h2>
                <ul class="space-y-3 text-sm leading-relaxed text-gray-300">
                    @if($siteContent?->contact_email)
                    <li><a href="mailto:{{ $siteContent->contact_email }}" class="break-words transition hover:text-yellow-500">{{ $siteContent->contact_email }}</a></li>
                    @endif
                    @if($siteContent?->contact_phone)
                    <li><a href="tel:{{ $siteContent->contact_phone }}" class="transition hover:text-yellow-500">{{ $siteContent->contact_phone }}</a></li>
                    @endif
                    @if($siteContent?->contact_address)
                    <li class="whitespace-pre-line text-gray-400">{{ $siteContent->contact_address }}</li>
                    @endif
                    @if(!$siteContent?->contact_email && !$siteContent?->contact_phone && !$siteContent?->contact_address)
                    <li class="text-gray-500">Kontak belum tersedia.</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-zinc-800">
            <div class="container mx-auto px-6 py-5">
                @if($siteContent?->instagram_url || $siteContent?->youtube_url || $siteContent?->tiktok_url)
                <div class="mb-5 flex justify-center gap-3" data-reveal>
                    @foreach(['Instagram' => $siteContent?->instagram_url, 'YouTube' => $siteContent?->youtube_url, 'TikTok' => $siteContent?->tiktok_url] as $platform => $url)
                    @if($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="HMVE di {{ $platform }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-500 text-xs font-bold text-black transition hover:-translate-y-1 hover:bg-yellow-400">{{ ['Instagram' => 'IG', 'YouTube' => 'YT', 'TikTok' => 'TT'][$platform] }}</a>
                    @endif
                    @endforeach
                </div>
                @endif
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-center text-xs text-gray-500 sm:text-left">© {{ date('Y') }} Himpunan Mahasiswa Vokasi Elektro-Elektronika.</p>
                    <a href="#top" aria-label="Kembali ke atas" class="flex h-9 w-9 items-center justify-center bg-yellow-500 text-lg font-bold text-black transition hover:bg-yellow-400">↑</a>
                </div>
            </div>
        </div>
    </footer>

    @include('components.toast-notifications')
</body>
</html>
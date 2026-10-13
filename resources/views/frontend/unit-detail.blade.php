<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anggota {{ $unit->nama }} - HMVE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-gray-200 antialiased font-sans">

    <div class="border-b border-yellow-500/20 bg-black">
        <div class="container mx-auto flex items-center justify-between gap-3 px-4 py-4 sm:px-6 sm:py-5">
            <a href="{{ route('home') }}" aria-label="HMVE Beranda" class="flex items-center gap-3">
                <img src="{{ asset('images/hmve.jpg') }}" alt="Logo HMVE" class="h-8 w-8 object-contain sm:h-9 sm:w-9">
                <span class="text-base font-bold tracking-widest text-white sm:text-lg">HMVE</span>
            </a>
            <a href="{{ route('home') }}#struktur" class="text-right text-xs uppercase tracking-widest text-gray-400 transition hover:text-yellow-500 sm:text-sm">&larr; Struktur</a>
        </div>
    </div>

    <header data-reveal class="container mx-auto max-w-5xl px-4 py-10 text-center sm:px-6 sm:py-12">
        <span class="text-xs bg-yellow-500 px-3 py-1 font-bold uppercase text-black">{{ $unit->kategori }}</span>
        <h1 class="mt-5 break-words text-2xl font-bold text-white sm:text-4xl">{{ $unit->nama }}</h1>
        <p class="mt-3 text-sm font-semibold uppercase tracking-widest text-yellow-500">Pengurus dan Anggota</p>
        <p class="mx-auto mt-5 max-w-3xl whitespace-pre-line leading-relaxed text-gray-300">{{ $unit->deskripsi }}</p>
    </header>

    <main class="container mx-auto max-w-5xl space-y-12 px-4 pb-16 sm:space-y-16 sm:px-6 sm:pb-20">
        <section data-reveal aria-label="Foto bersama {{ $unit->nama }}" class="overflow-hidden">
            @if($unit->foto_bersama)
            <img src="{{ asset($unit->foto_bersama) }}" alt="Foto bersama {{ $unit->nama }}" class="max-h-[36rem] w-full bg-zinc-950 object-cover transition-transform duration-700 hover:scale-[1.02]">
            @else
            <div class="flex aspect-video w-full items-center justify-center border border-zinc-800 bg-zinc-950 text-center text-sm uppercase tracking-widest text-zinc-500">Foto bersama {{ $unit->nama }} belum tersedia</div>
            @endif
        </section>

        <div>
            <h2 class="mb-6 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase text-white sm:text-2xl">Kepala & Wakil Kepala</h2>
            <div class="grid grid-cols-1 gap-x-12 gap-y-12 md:grid-cols-2" data-reveal-group>
                @forelse($kepala->concat($wakil) as $a)
                <div data-reveal class="group flex flex-col items-start">
                    <div class="relative w-full aspect-[4/5] mb-6 overflow-hidden bg-black rounded-sm">
                        @if($a->image_url)
                        <img src="{{ filter_var($a->image_url, FILTER_VALIDATE_URL) ? $a->image_url : asset($a->image_url) }}" alt="{{ $a->nama }}" class="w-full h-full object-contain p-4">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-6xl font-bold text-zinc-700">{{ mb_substr($a->nama, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="border-t border-zinc-800 pt-4 w-full">
                        <h3 class="mb-1 break-words text-xl font-serif text-white sm:text-2xl">{{ $a->nama }}</h3>
                        <p class="text-yellow-500 text-sm uppercase tracking-wider font-semibold">{{ $a->jabatan }}</p>
                    </div>
                </div>
                @empty
                <p class="col-span-full text-gray-500">Data kepala dan wakil belum tersedia.</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="mb-6 border-l-4 border-yellow-500 pl-3 text-lg font-bold uppercase text-white sm:text-2xl">Staff</h2>
            <div class="grid grid-cols-1 gap-x-12 gap-y-12 md:grid-cols-2" data-reveal-group>
                @forelse($staff as $a)
            <div data-reveal class="group flex flex-col items-start">
                <div class="relative w-full aspect-[4/5] mb-6 overflow-hidden bg-black rounded-sm">
                    @if($a->image_url)
                    <img src="{{ filter_var($a->image_url, FILTER_VALIDATE_URL) ? $a->image_url : asset($a->image_url) }}" alt="{{ $a->nama }}" class="w-full h-full object-contain p-4">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-6xl font-bold text-zinc-700">{{ mb_substr($a->nama, 0, 1) }}</div>
                    @endif
                </div>
                <div class="border-t border-zinc-800 pt-4 w-full">
                    <h3 class="mb-1 break-words text-xl font-serif text-white sm:text-2xl">{{ $a->nama }}</h3>
                    <p class="text-yellow-500 text-sm uppercase tracking-wider font-semibold mb-4">{{ $a->jabatan }}</p>
                    <p class="text-gray-400 text-sm leading-relaxed">{{ $a->deskripsi }}</p>
                </div>
            </div>
            @empty
            <p class="col-span-full text-gray-500">Belum ada staff terdaftar untuk unit ini.</p>
            @endforelse
            </div>
        </div>
    </main>

    @include('components.toast-notifications')
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bergabung dengan HMVE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-black text-gray-200 antialiased font-sans">
    <nav class="w-full border-b border-yellow-500/30 bg-black/90">
        <div class="container mx-auto flex items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4">
            <a href="{{ route('home') }}" aria-label="HMVE Beranda" class="flex shrink-0 items-center gap-3">
                <img src="{{ asset('images/hmve.jpg') }}" alt="Logo HMVE" class="h-9 w-9 object-contain sm:h-10 sm:w-10">
                <span class="text-xl font-bold tracking-widest text-white sm:text-2xl">HMVE</span>
            </a>
            <a href="{{ route('home') }}" class="max-w-32 text-right text-xs font-semibold uppercase tracking-widest text-gray-300 transition hover:text-yellow-500 sm:max-w-none sm:text-sm">Kembali ke Beranda</a>
        </div>
    </nav>

    <main class="min-h-[70vh] border-b border-zinc-900 px-4 py-16 sm:px-6 sm:py-28">
        <section data-reveal class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-yellow-500 sm:text-sm">Himpunan Mahasiswa Vokasi Elektro-Elektronika</p>
            <h1 class="mt-5 break-words text-2xl font-extrabold uppercase text-white sm:text-4xl md:text-5xl">{{ $siteContent?->join_title ?: 'Bergabung dengan HMVE' }}</h1>
            <div class="mx-auto mt-6 h-1 w-20 bg-yellow-500"></div>
            <p class="mx-auto mt-8 max-w-2xl whitespace-pre-line text-sm leading-relaxed text-gray-300 sm:text-base">{{ $siteContent?->join_description ?: 'Tertarik belajar, berkarya, dan berkembang bersama mahasiswa elektro-elektronika? Hubungi HMVE untuk mendapatkan informasi pendaftaran anggota.' }}</p>

            @if($siteContent?->join_url)
            <a href="{{ $siteContent->join_url }}" target="_blank" rel="noopener noreferrer" class="mt-9 inline-block bg-yellow-500 px-7 py-3 font-bold text-black transition hover:bg-yellow-400">
                {{ $siteContent->join_label ?: 'Daftar Sekarang' }}
            </a>
            @elseif($siteContent?->contact_email)
            <a href="mailto:{{ $siteContent->contact_email }}?subject={{ rawurlencode('Informasi pendaftaran anggota HMVE') }}" class="mt-9 inline-block bg-yellow-500 px-7 py-3 font-bold text-black transition hover:bg-yellow-400">
                Hubungi HMVE
            </a>
            @else
            <p class="mt-9 text-sm text-gray-400">Informasi pendaftaran dapat diperoleh melalui kanal media sosial HMVE.</p>
            @endif

            @if($siteContent && ($siteContent->instagram_url || $siteContent->youtube_url || $siteContent->tiktok_url))
            <div class="mt-12 flex flex-wrap justify-center gap-x-6 gap-y-3 border-t border-zinc-800 pt-6" data-reveal-group>
                @foreach(['Instagram' => $siteContent->instagram_url, 'YouTube' => $siteContent->youtube_url, 'TikTok' => $siteContent->tiktok_url] as $platform => $url)
                @if($url)
                <a data-reveal href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-gray-300 underline underline-offset-4 transition hover:text-yellow-500">{{ $platform }}</a>
                @endif
                @endforeach
            </div>
            @endif
        </section>
    </main>

    <footer class="px-6 py-8 text-center text-sm text-gray-500">
        <p>© 2026 Himpunan Mahasiswa Vokasi Elektro-Elektronika.</p>
    </footer>
    @include('components.toast-notifications')
</body>
</html>
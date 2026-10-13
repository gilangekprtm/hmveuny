<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Control Panel HMVE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-black px-4 py-8 text-gray-200 antialiased sm:px-6">
    <main class="w-full max-w-sm border border-zinc-800 bg-zinc-950 p-6 sm:p-8">
        <h1 class="text-xl font-bold tracking-widest text-white sm:text-2xl">C-PANEL HMVE</h1>
        <p class="mt-2 text-sm text-gray-400">Masuk untuk melanjutkan.</p>

        <form action="{{ route('admin.login.store') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-gray-300">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus class="w-full border border-zinc-700 bg-black px-4 py-3 text-white outline-none focus:border-yellow-500">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-gray-300">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full border border-zinc-700 bg-black px-4 py-3 text-white outline-none focus:border-yellow-500">
            </div>
            <button type="submit" class="w-full bg-yellow-500 py-3 font-bold text-black transition hover:bg-yellow-400">Masuk</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">Belum punya akun? <a href="{{ route('admin.register') }}" class="text-yellow-500 transition hover:text-yellow-400">Daftar</a></p>
        <a href="{{ route('home') }}" class="mt-6 block text-center text-sm text-gray-400 transition hover:text-yellow-500">Kembali ke website</a>
    </main>
    @include('components.toast-notifications')
</body>
</html>

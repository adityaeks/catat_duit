<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - Catat Duit</title>
    
    <!-- PWA Meta Tags & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B0F19">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Catat Duit">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite CSS/JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after {
            -webkit-touch-callout: none;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        input, textarea, [contenteditable="true"] {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>
</head>
<body class="min-h-screen bg-[#0B0F19] text-slate-100 antialiased flex items-center justify-center p-4 select-none">

    <div class="w-full max-w-md bg-[#111726] border border-white/[0.08] rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6">
        
        <!-- Logo & Title -->
        <div class="text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mx-auto shadow-inner">
                <x-icon name="wallet" class="w-7 h-7 stroke-[2.2]" />
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-white">Catat Duit</h1>
                <p class="text-xs text-slate-400 font-normal mt-1">Platform pencatatan keuangan & anggaran personal</p>
            </div>
        </div>

        <!-- Error & Alert Messages -->
        @if(session('success'))
            <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <x-icon name="check" class="w-4 h-4 flex-shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-semibold flex items-center gap-2">
                <x-icon name="alert-circle" class="w-4 h-4 flex-shrink-0" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Email Input -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-400">Email Akun</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <input type="email" 
                           name="email" 
                           id="login_email"
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email"
                           placeholder="nama@email.com" 
                           class="w-full pl-10 pr-4 py-3 bg-[#0B0F19] border border-white/[0.1] rounded-2xl text-sm font-medium text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
                </div>
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-400">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" 
                           name="password" 
                           id="login_password"
                           required 
                           placeholder="••••••••" 
                           class="w-full pl-10 pr-4 py-3 bg-[#0B0F19] border border-white/[0.1] rounded-2xl text-sm font-medium text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
                </div>
            </div>

            <!-- Persistent Session Notice (Active Always) -->
            <div class="px-3.5 py-2.5 bg-white/[0.03] rounded-2xl border border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1.5 font-medium">
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-400" />
                    <span>Tetap masuk di perangkat ini</span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 font-bold">Otomatis</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                <span>Masuk Sekarang</span>
            </button>
        </form>

    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('PWA Service Worker registered:', reg.scope))
                    .catch((err) => console.log('PWA Service Worker registration failed:', err));
            });
        }
    </script>
</body>
</html>

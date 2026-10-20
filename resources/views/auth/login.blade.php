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
<body class="min-h-screen surface-canvas text-slate-100 antialiased flex items-center justify-center p-4 select-none">

    <div class="w-full max-w-md surface-card rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6">
        
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

        <!-- Google OAuth Quick Button -->
        <a href="{{ route('auth.google') }}" 
           class="w-full py-3.5 px-4 bg-white hover:bg-slate-100 active:scale-[0.98] text-slate-900 font-bold rounded-2xl shadow-xl transition-all text-xs sm:text-sm flex items-center justify-center gap-3 cursor-pointer border border-white/20">
            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Masuk dengan Google</span>
        </a>

        <!-- Divider -->
        <div class="relative flex items-center justify-center">
            <div class="border-t border-white/[0.08] w-full"></div>
            <!-- <span class="bg-[#111726] px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider relative">atau dengan email</span> -->
        </div>

        <!-- Traditional Email Login Form -->
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
                           class="w-full pl-10 pr-4 py-3 surface-inset rounded-2xl text-sm font-medium text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
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
                           class="w-full pl-10 pr-4 py-3 surface-inset rounded-2xl text-sm font-medium text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
                </div>
            </div>

            <!-- Persistent Session Notice (Active Always) -->
            <!-- <div class="px-3.5 py-2.5 bg-white/[0.03] rounded-2xl border border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
                <span class="flex items-center gap-1.5 font-medium">
                    <x-icon name="check" class="w-3.5 h-3.5 text-emerald-400" />
                    <span>Tetap masuk di perangkat ini</span>
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 font-bold">Otomatis</span>
            </div> -->

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

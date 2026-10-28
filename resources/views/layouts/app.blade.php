<!DOCTYPE html>
<html lang="id" class="dark min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Catat Duit - Kelola Keuangan & Anggaran' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags & Icons -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#059669">
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
        /* Custom scrollbar for mobile feel */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* SweetAlert2 Obsidian Luxury Theme */
        div:where(.swal2-container).swal2-backdrop-show {
            background: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }
        div:where(.swal2-popup) {
            background: #111726 !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 1.5rem !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9) !important;
            color: #F8FAFC !important;
            padding: 1.5rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        div:where(.swal2-title) {
            color: #F8FAFC !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            letter-spacing: -0.025em !important;
        }
        div:where(.swal2-html-container) {
            color: #94A3B8 !important;
            margin: 0.5rem 0 0 0 !important;
            font-size: 0.85rem !important;
        }
        div:where(.swal2-icon).swal2-success {
            border-color: #10B981 !important;
            color: #10B981 !important;
        }
        div:where(.swal2-icon).swal2-success [class^='swal2-success-line'] {
            background-color: #10B981 !important;
        }
        div:where(.swal2-icon).swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        div:where(.swal2-icon).swal2-success .swal2-success-fix,
        div:where(.swal2-icon).swal2-success [class^='swal2-success-circular-line'] {
            background-color: transparent !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#07090E] text-slate-100 antialiased flex flex-col justify-start items-center select-none font-sans">

    <!-- App Frame Canvas (Seamless full-bleed on mobile, luxury spacious dashboard on desktop) -->
    <div class="w-full max-w-md lg:max-w-7xl min-h-screen lg:min-h-[92vh] surface-canvas text-slate-100 sm:my-6 sm:rounded-3xl sm:border sm:border-white/[0.08] sm:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.85)] relative flex flex-col lg:flex-row flex-1 lg:overflow-hidden">
        
        <!-- Desktop Luxury Sidebar (Hidden on mobile < lg) -->
        <aside class="hidden lg:flex lg:w-64 xl:w-72 lg:flex-col lg:justify-between lg:border-r lg:border-white/[0.08] lg:bg-[#07090E]/80 lg:backdrop-blur-md p-6 flex-shrink-0 z-30">
            
            <div class="space-y-6">
                <!-- Branding Header -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/10">
                        <x-icon name="wallet" class="w-5 h-5 stroke-[2.2]" />
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-base font-extrabold tracking-tight text-white leading-tight">Catat Duit</h1>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">PRO</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                <!-- Desktop CTA Buttons -->
                <div class="space-y-2 pt-2">
                    <button type="button" 
                            onclick="openTransactionModal()" 
                            class="w-full py-2.5 px-3.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.98] cursor-pointer">
                        <x-icon name="plus" class="w-4 h-4 stroke-[2.8]" />
                        <span>Catat Transaksi</span>
                    </button>
                    <button type="button" 
                            onclick="openTransactionModal(); setTimeout(() => document.getElementById('ocrReceiptInput')?.click(), 150);" 
                            class="w-full py-2 px-3 surface-card hover:surface-elevated text-emerald-300 hover:text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 border border-emerald-500/20 transition-all cursor-pointer">
                        <x-icon name="camera" class="w-3.5 h-3.5 text-emerald-400" />
                        <span>Pindai / Scan Struk</span>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1.5 pt-2">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}">
                        <x-icon name="home" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                        <span>Beranda</span>
                    </a>

                    <a href="{{ route('transactions.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('transactions.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}">
                        <x-icon name="receipt" class="w-4 h-4 {{ request()->routeIs('transactions.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                        <span>Transaksi</span>
                    </a>

                    <a href="{{ route('budgets.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('budgets.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}">
                        <x-icon name="target" class="w-4 h-4 {{ request()->routeIs('budgets.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                        <span>Anggaran</span>
                    </a>

                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('categories.*') ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-white/[0.04]' }}">
                        <x-icon name="tags" class="w-4 h-4 {{ request()->routeIs('categories.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                        <span>Kategori</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar Footer -->
            <div class="pt-6 border-t border-white/[0.08] space-y-3">
                <button type="button" 
                        onclick="openReminderModal()" 
                        class="w-full flex items-center justify-between p-2.5 rounded-xl surface-inset hover:surface-card text-xs text-slate-300 hover:text-white transition-all cursor-pointer">
                    <span class="flex items-center gap-2">
                        <x-icon name="bell" class="w-4 h-4 text-emerald-400" />
                        <span>Pengingat 17:00</span>
                    </span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </button>

                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        @if(auth()->user()?->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-xl object-cover border border-white/[0.08]" referrerpolicy="no-referrer">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-slate-800 border border-white/[0.08] flex items-center justify-center font-bold text-xs text-slate-300">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="button" 
                                onclick="confirmAction(this.form, 'Keluar dari Akun?', 'Apakah Anda yakin ingin keluar dari aplikasi?', 'Ya, Keluar')" 
                                title="Keluar" 
                                class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Main Content Area Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 lg:overflow-y-auto">
            
            <!-- Desktop Top Navigation Bar (Hidden on Mobile) -->
            <header class="hidden lg:flex items-center justify-between px-8 py-4 border-b border-white/[0.06] bg-[#0B0F19]/40 backdrop-blur-md flex-shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                        @if(request()->routeIs('dashboard'))
                            Dashboard Finansial
                        @elseif(request()->routeIs('transactions.*'))
                            Riwayat Transaksi
                        @elseif(request()->routeIs('budgets.*'))
                            Manajemen Anggaran
                        @elseif(request()->routeIs('categories.*'))
                            Daftar Pos Kategori
                        @else
                            Catat Duit
                        @endif
                    </span>
                    <span class="text-white/20">•</span>
                    <span class="text-xs text-slate-300 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            onclick="openReminderModal()" 
                            class="p-2 surface-card hover:surface-elevated text-slate-400 hover:text-white rounded-xl transition-all cursor-pointer"
                            title="Pengingat Jam 5 Sore">
                        <x-icon name="bell" class="w-4 h-4" />
                    </button>
                    <button type="button" 
                            onclick="openTransactionModal()" 
                            class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <x-icon name="plus" class="w-3.5 h-3.5 stroke-[2.8]" />
                        <span>Catat Transaksi</span>
                    </button>
                </div>
            </header>

            <!-- Flash Message Toast -->
            @if(session('success'))
                <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm surface-elevated text-white px-4 py-3 rounded-2xl shadow-2xl border border-emerald-500/40 flex items-center gap-3 transition-all duration-300">
                    <div class="w-7 h-7 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <x-icon name="check" class="w-4 h-4 stroke-[2.5]" />
                    </div>
                    <span class="text-xs font-semibold flex-1 text-slate-200">{{ session('success') }}</span>
                    <button type="button" onclick="document.getElementById('flash-toast').remove()" class="text-slate-400 hover:text-white p-1">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm surface-elevated text-white px-4 py-3 rounded-2xl shadow-2xl border border-rose-500/40 flex items-center gap-3 transition-all duration-300">
                    <div class="w-7 h-7 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0">
                        <x-icon name="alert-circle" class="w-4 h-4" />
                    </div>
                    <span class="text-xs font-semibold flex-1 text-slate-200">{{ session('error') }}</span>
                    <button type="button" onclick="document.getElementById('flash-toast').remove()" class="text-slate-400 hover:text-white p-1">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm surface-elevated text-white px-4 py-3 rounded-2xl shadow-2xl border border-rose-500/40 flex flex-col gap-1 transition-all duration-300">
                    <div class="flex items-center gap-2 font-bold text-xs text-rose-400">
                        <x-icon name="alert-circle" class="w-4 h-4" />
                        <span>Terjadi kesalahan input:</span>
                    </div>
                    <ul class="text-[11px] list-disc list-inside space-y-0.5 text-slate-300 pl-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PWA Install Prompt Banner -->
            <div id="pwaInstallBanner" class="hidden mx-4 mt-3 p-3 surface-elevated text-white rounded-2xl flex items-center justify-between gap-3 shadow-lg">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <x-icon name="wallet" class="w-4 h-4 stroke-[2.2]" />
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-bold leading-tight truncate text-white">Pasang Catat Duit</p>
                        <p class="text-[10px] text-slate-400 truncate">Akses cepat & nyaman dari beranda HP</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <button type="button" onclick="triggerPwaInstall()" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold rounded-xl transition-all active:scale-95 cursor-pointer">
                        Pasang
                    </button>
                    <button type="button" onclick="document.getElementById('pwaInstallBanner').classList.add('hidden')" class="p-1 text-slate-400 hover:text-white rounded-lg cursor-pointer">
                        <x-icon name="x" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 pb-28 lg:pb-8 lg:p-6">
                @yield('content')
            </main>

        </div>

        <!-- Bottom Navigation Bar (Fintech Refined Fixed Dock - Mobile Only) -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 mx-auto w-full max-w-md surface-dock px-3 pt-2 pb-3 sm:pb-2 sm:rounded-t-2xl lg:hidden shadow-[0_-10px_30px_-5px_rgba(0,0,0,0.8)] backdrop-blur-xl" style="padding-bottom: max(0.65rem, env(safe-area-inset-bottom));">
            <div class="flex items-center justify-around relative">
                
                <!-- Tab Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    <x-icon name="home" class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                    <span class="text-[10px] tracking-tight">Beranda</span>
                </a>

                <!-- Tab Transaksi -->
                <a href="{{ route('transactions.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('transactions.*') ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    <x-icon name="receipt" class="w-5 h-5 {{ request()->routeIs('transactions.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                    <span class="text-[10px] tracking-tight">Transaksi</span>
                </a>

                <!-- Quick Add Center Action -->
                <div class="flex items-center justify-center">
                    <button type="button" 
                            onclick="openTransactionModal()" 
                            aria-label="Catat Transaksi Baru"
                            class="w-11 h-11 bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 rounded-2xl flex items-center justify-center transition-all shadow-md shadow-emerald-500/25 focus:outline-none cursor-pointer">
                        <x-icon name="plus" class="w-5 h-5 stroke-[2.8]" />
                    </button>
                </div>

                <!-- Tab Anggaran / Budget -->
                <a href="{{ route('budgets.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('budgets.*') ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    <x-icon name="target" class="w-5 h-5 {{ request()->routeIs('budgets.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                    <span class="text-[10px] tracking-tight">Anggaran</span>
                </a>

                <!-- Tab Kategori -->
                <a href="{{ route('categories.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('categories.*') ? 'text-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">
                    <x-icon name="tags" class="w-5 h-5 {{ request()->routeIs('categories.*') ? 'stroke-[2.2]' : 'stroke-[1.8]' }}" />
                    <span class="text-[10px] tracking-tight">Kategori</span>
                </a>

            </div>
        </nav>

        <!-- Global Daily Reminder Modal (Jam 17:00) -->
        <div id="reminderModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
            <div class="surface-elevated w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] transform transition-all animate-in fade-in duration-200">
                
                <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center flex-shrink-0">
                            <x-icon name="bell" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white leading-tight">Pengingat Harian</h3>
                            <p class="text-[11px] text-slate-400">Notifikasi jam 5 sore (17:00 WIB)</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeReminderModal()" class="p-1.5 text-slate-400 hover:text-white rounded-xl hover:bg-white/[0.06] transition-all cursor-pointer">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <!-- Toggle Card -->
                    <div class="p-4 surface-card rounded-2xl flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-white">Aktifkan Pengingat</p>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Kirim notifikasi setiap sore agar tidak lupa mencatat</p>
                            <div id="reminderStatusText" class="mt-1.5 text-[11px]"></div>
                        </div>
                        <!-- Modern Style Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" id="reminderToggleInput" onchange="toggleDailyReminder()" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Info Box -->
                    <div class="p-3.5 surface-inset rounded-2xl space-y-2 text-xs">
                        <div class="flex items-center gap-2 text-slate-300 font-semibold text-[11px]">
                            <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" />
                            <span>Jadwal Pengiriman: 17:00 WIB</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Pesan yang akan muncul di HP Anda:
                        </p>
                        <div class="p-2.5 bg-black/40 border border-white/[0.06] rounded-xl text-[11px] text-slate-300">
                            <p class="font-bold text-white flex items-center gap-1.5">
                                <span>Catat Duit 💰</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-emerald-500/20 text-emerald-300 rounded font-normal">17:00</span>
                            </p>
                            <p class="text-slate-400 mt-0.5">Sudah jam 5 sore nih! Jangan lupa catat pengeluaran hari ini ya.</p>
                        </div>
                    </div>

                    <!-- Test Button -->
                    <button type="button" 
                            id="testReminderBtn"
                            onclick="testReminderNotification()" 
                            class="w-full py-3 surface-card hover:surface-elevated active:scale-[0.98] text-slate-200 hover:text-white font-semibold rounded-2xl transition-all text-xs flex items-center justify-center gap-2 cursor-pointer border border-white/[0.08]">
                        <x-icon name="sparkles" class="w-4 h-4 text-emerald-400" />
                        <span>🔔 Uji Coba Notifikasi Sekarang</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- Global Quick Transaction Modal -->
        <div id="quickTransactionModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
            <div class="surface-elevated w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] transform transition-all animate-in fade-in duration-200 max-h-[92vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <x-icon name="wallet" class="w-4 h-4 text-emerald-400" />
                        <span>Catat Transaksi</span>
                    </h3>
                    <button type="button" onclick="closeTransactionModal()" class="p-1.5 text-slate-400 hover:text-white rounded-xl hover:bg-white/[0.06] transition-all cursor-pointer">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <!-- OCR Receipt Scanner Action -->
                <div class="mt-3">
                    <input type="file" id="ocrReceiptInput" accept="image/*" capture="environment" class="hidden" onchange="handleReceiptScan(this)">
                    <button type="button" 
                            id="scanReceiptBtn"
                            onclick="document.getElementById('ocrReceiptInput').click()" 
                            class="w-full py-2.5 px-3.5 surface-inset hover:surface-card border border-emerald-500/25 hover:border-emerald-500/40 rounded-2xl flex items-center justify-between text-xs font-semibold text-emerald-300 transition-all cursor-pointer group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                <x-icon name="camera" class="w-4 h-4" />
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-white text-xs">Pindai / Foto Struk</p>
                                <p class="text-[10px] text-slate-400 font-normal">Otomatis isi nominal & pos belanja</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded-lg bg-emerald-500/15 text-emerald-300 text-[10px] font-bold border border-emerald-500/25 flex items-center gap-1">
                            <x-icon name="sparkles" class="w-3 h-3 text-emerald-400" />
                            <span>Scan</span>
                        </span>
                    </button>
                </div>

                <!-- Shared / OCR Data Auto-Fill Badge -->
                <div id="sharedDataBadge" class="hidden mt-3 p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-xl text-xs text-emerald-300 flex items-center justify-between gap-2 font-medium">
                    <div class="flex items-center gap-2">
                        <x-icon name="sparkles" class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                        <span id="sharedDataBadgeText">Data struk/pembayaran terdeteksi & terisi otomatis</span>
                    </div>
                    <button type="button" id="btnViewOcrText" onclick="toggleOcrRawText()" class="text-[10px] underline text-emerald-400 hover:text-white cursor-pointer hidden">Lihat Teks</button>
                </div>
                <div id="ocrRawTextContainer" class="hidden mt-2 p-2.5 surface-inset rounded-xl text-[10px] text-slate-400 max-h-24 overflow-y-auto whitespace-pre-wrap font-mono select-text"></div>

                <form action="{{ route('transactions.store') }}" method="POST" class="mt-4 space-y-4" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); btn.innerHTML = '<span>Menyimpan...</span>'; }">
                    @csrf
                    
                    <!-- Segmented Control: Pengeluaran vs Pemasukan -->
                    <div class="grid grid-cols-2 p-1 surface-inset rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" checked class="peer sr-only" onchange="toggleTypeCategories('expense')">
                            <div class="py-2 text-center text-xs font-semibold rounded-lg text-slate-400 peer-checked:bg-rose-500/20 peer-checked:text-rose-300 peer-checked:border peer-checked:border-rose-500/30 transition-all flex items-center justify-center gap-1.5">
                                <x-icon name="arrow-down-left" class="w-3.5 h-3.5" />
                                <span>Pengeluaran</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="income" class="peer sr-only" onchange="toggleTypeCategories('income')">
                            <div class="py-2 text-center text-xs font-semibold rounded-lg text-slate-400 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-300 peer-checked:border peer-checked:border-emerald-500/30 transition-all flex items-center justify-center gap-1.5">
                                <x-icon name="arrow-up-right" class="w-3.5 h-3.5" />
                                <span>Pemasukan</span>
                            </div>
                        </label>
                    </div>

                    <!-- Nominal Input with Rupiah Autoformat -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nominal Transaksi</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-base">Rp</span>
                            <input type="text" 
                                   id="display_amount" 
                                   inputmode="numeric"
                                   placeholder="0" 
                                   required 
                                   class="w-full pl-12 pr-4 py-3 surface-inset rounded-xl text-xl font-bold text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 num-tabular select-text transition-all"
                                   oninput="formatRupiahInput(this, 'real_amount')">
                            <input type="hidden" name="amount" id="real_amount" required>
                        </div>
                    </div>

                    <!-- Kategori Selector -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Kategori</label>
                        <select name="category_id" id="modal_category_id" required class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:border-emerald-500 select-text">
                            <!-- Populated dynamically / pre-rendered -->
                            @php
                                $allCats = auth()->check() ? auth()->user()->categories()->orderBy('name')->get() : collect();
                            @endphp
                            @foreach($allCats as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" class="bg-[#111726] text-white">{{ $cat->name }} ({{ $cat->type == 'expense' ? 'Pengeluaran' : 'Pemasukan' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Transaksi -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal</label>
                        <input type="date" 
                               name="transaction_date" 
                               value="{{ date('Y-m-d') }}" 
                               required 
                               class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:border-emerald-500 select-text">
                    </div>

                    <!-- Catatan / Keterangan -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan / Catatan (Opsional)</label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Makan siang, Bensin, dll." 
                               class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 select-text">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl shadow-md shadow-emerald-500/20 transition-all text-xs flex items-center justify-center gap-1.5">
                        <x-icon name="check" class="w-4 h-4 stroke-[2.5]" />
                        <span>Simpan Transaksi</span>
                    </button>
                </form>

            </div>
        </div>

    </div>

    <!-- JavaScript Helper -->
    <script>
        function openTransactionModal() {
            document.getElementById('quickTransactionModal').classList.remove('hidden');
            setTimeout(() => {
                const amountInput = document.getElementById('display_amount');
                if (amountInput) amountInput.focus();
            }, 100);
            toggleTypeCategories('expense');
        }

        function closeTransactionModal() {
            document.getElementById('quickTransactionModal').classList.add('hidden');
        }

        function toggleTypeCategories(type) {
            const select = document.getElementById('modal_category_id');
            if (!select) return;
            let firstVisible = null;
            Array.from(select.options).forEach(option => {
                const optType = option.getAttribute('data-type');
                if (optType === type) {
                    option.hidden = false;
                    option.disabled = false;
                    if (!firstVisible) firstVisible = option;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });
            if (firstVisible) {
                select.value = firstVisible.value;
            }
        }

        function formatRupiahInput(input, hiddenId) {
            let value = input.value.replace(/[^0-9]/g, '');
            let hiddenInput = document.getElementById(hiddenId);
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            if (value === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }

        // OCR Receipt Scanner Handler (Powered by OCR.space)
        async function handleReceiptScan(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];

            if (file.size > 5 * 1024 * 1024) {
                if (window.Swal) {
                    window.Swal.fire({
                        background: '#111726',
                        color: '#F8FAFC',
                        buttonsStyling: false,
                        customClass: {
                            popup: '!bg-[#111726] !border !border-white/10 !rounded-3xl !p-6 !shadow-2xl !max-w-sm',
                            confirmButton: 'w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer'
                        },
                        html: `
                            <div class="flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-3 shadow-lg shadow-amber-500/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-extrabold text-white tracking-tight">Ukuran Foto Terlalu Besar</h3>
                                <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">Maksimal ukuran file foto adalah 5MB. Silakan pilih foto dengan resolusi lebih wajar.</p>
                            </div>
                        `,
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    alert('Ukuran foto maksimal 5MB.');
                }
                input.value = '';
                return;
            }

            const scanBtn = document.getElementById('scanReceiptBtn');
            const originalBtnHtml = scanBtn ? scanBtn.innerHTML : '';
            if (scanBtn) {
                scanBtn.disabled = true;
                scanBtn.innerHTML = `
                    <div class="flex items-center gap-2 text-emerald-400 py-1">
                        <svg class="animate-spin w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                        <span class="text-xs font-bold text-white">Memproses struk dengan OCR.space...</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Mohon tunggu</span>
                `;
            }

            const formData = new FormData();
            formData.append('receipt', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("transactions.scan-receipt") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal membaca struk.');
                }

                // 1. Fill Nominal
                if (data.amount && data.amount > 0) {
                    const realAmount = document.getElementById('real_amount');
                    const displayAmount = document.getElementById('display_amount');
                    if (realAmount && displayAmount) {
                        realAmount.value = data.amount;
                        displayAmount.value = new Intl.NumberFormat('id-ID').format(data.amount);
                    }
                }

                // 2. Fill Tanggal
                if (data.transaction_date) {
                    const dateInput = document.querySelector('input[name="transaction_date"]');
                    if (dateInput) {
                        dateInput.value = data.transaction_date;
                    }
                }

                // 3. Fill Keterangan
                if (data.description) {
                    const descInput = document.querySelector('input[name="description"]');
                    if (descInput) {
                        descInput.value = data.description;
                    }
                }

                // 4. Fill Tipe & Kategori
                if (data.type) {
                    const typeRadio = document.querySelector(`input[name="type"][value="${data.type}"]`);
                    if (typeRadio) {
                        typeRadio.checked = true;
                        toggleTypeCategories(data.type);
                    }
                }

                if (data.category_id) {
                    const catSelect = document.getElementById('modal_category_id');
                    if (catSelect) {
                        catSelect.value = data.category_id;
                    }
                }

                // 5. Update Status Banner
                const badge = document.getElementById('sharedDataBadge');
                const badgeText = document.getElementById('sharedDataBadgeText');
                const btnView = document.getElementById('btnViewOcrText');
                const rawBox = document.getElementById('ocrRawTextContainer');

                if (badge && badgeText) {
                    badge.classList.remove('hidden');
                    const amountFormatted = data.amount ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data.amount) : 'Nominal belum terdeteksi';
                    badgeText.innerText = `Struk berhasil dipindai: ${data.description || 'Pembelian'} (${amountFormatted})`;
                }

                if (data.raw_text && rawBox && btnView) {
                    rawBox.innerText = data.raw_text;
                    btnView.classList.remove('hidden');
                }

                if (window.Swal) {
                    const formattedAmount = data.amount ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data.amount) : 'Nominal tidak terdeteksi';
                    const merchantName = (data.description && data.description.trim()) ? data.description : 'Tidak terdeteksi';
                    const trxDate = (data.transaction_date && data.transaction_date.trim()) ? data.transaction_date : 'Hari ini';

                    window.Swal.fire({
                        background: '#111726',
                        color: '#F8FAFC',
                        showConfirmButton: true,
                        confirmButtonText: 'Lanjutkan & Tinjau Form',
                        buttonsStyling: false,
                        customClass: {
                            popup: '!bg-[#111726] !border !border-white/10 !rounded-3xl !p-6 !shadow-2xl !max-w-sm',
                            confirmButton: 'w-full py-3 px-4 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl shadow-lg shadow-emerald-500/20 text-xs transition-all cursor-pointer'
                        },
                        html: `
                            <div class="flex flex-col items-center text-center">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-3 shadow-lg shadow-emerald-500/10">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-extrabold text-white tracking-tight">Struk Berhasil Dipindai! 🎉</h3>
                                <p class="text-xs text-slate-400 mt-1">Data transaksi berhasil diekstrak oleh AI OCR</p>
                            </div>

                            <div class="mt-4 p-3.5 rounded-2xl bg-[#07090E]/80 border border-white/5 space-y-2.5 text-left text-xs shadow-inner">
                                <div class="flex items-center justify-between pb-2 border-b border-white/5">
                                    <span class="text-[11px] font-medium text-slate-400">Total Belanja</span>
                                    <span class="text-sm font-extrabold text-emerald-400 font-mono tracking-tight">${formattedAmount}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-medium text-slate-400">Merchant / Toko</span>
                                    <span class="text-xs font-semibold text-slate-200 text-right truncate max-w-[170px]">${merchantName}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-medium text-slate-400">Tanggal</span>
                                    <span class="text-xs font-medium text-slate-300 text-right">${trxDate}</span>
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-400 mt-3.5 text-center leading-relaxed">
                                Form telah terisi otomatis. Silakan periksa kembali sebelum menyimpan transaksi.
                            </p>
                        `
                    });
                }
            } catch (err) {
                console.error('[OCR Error]', err);
                if (window.Swal) {
                    window.Swal.fire({
                        background: '#111726',
                        color: '#F8FAFC',
                        buttonsStyling: false,
                        confirmButtonText: 'Tutup',
                        customClass: {
                            popup: '!bg-[#111726] !border !border-white/10 !rounded-3xl !p-6 !shadow-2xl !max-w-sm',
                            confirmButton: 'w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 active:scale-[0.98] text-white font-bold rounded-xl text-xs transition-all cursor-pointer'
                        },
                        html: `
                            <div class="flex flex-col items-center text-center">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 mb-3 shadow-lg shadow-rose-500/10">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-extrabold text-white tracking-tight">Pindai Struk Gagal</h3>
                                <p class="text-xs text-slate-400 mt-2 leading-relaxed">${err.message || 'Terjadi kendala saat menghubungi OCR.space. Pastikan koneksi internet aktif dan struk terbaca jelas.'}</p>
                            </div>
                        `
                    });
                } else {
                    alert('Gagal memindai struk: ' + err.message);
                }
            } finally {
                if (scanBtn) {
                    scanBtn.disabled = false;
                    scanBtn.innerHTML = originalBtnHtml;
                }
                input.value = '';
            }
        }

        function toggleOcrRawText() {
            const rawBox = document.getElementById('ocrRawTextContainer');
            if (rawBox) {
                rawBox.classList.toggle('hidden');
            }
        }

        // Auto dismiss flash toast after 4s
        setTimeout(() => {
            const toast = document.getElementById('flash-toast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);

        // Check for Quick Add / Shared Receipt URL parameters on load
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('quick_add') === '1' || params.has('shared_amount')) {
                openTransactionModal();
                
                const sharedType = params.get('shared_type') || 'expense';
                const typeRadio = document.querySelector(`input[name="type"][value="${sharedType}"]`);
                if (typeRadio) {
                    typeRadio.checked = true;
                    toggleTypeCategories(sharedType);
                }

                const sharedCategory = params.get('shared_category_id');
                if (sharedCategory) {
                    const catSelect = document.getElementById('modal_category_id');
                    if (catSelect) catSelect.value = sharedCategory;
                }

                const sharedAmount = params.get('shared_amount');
                if (sharedAmount && parseInt(sharedAmount) > 0) {
                    const realAmount = document.getElementById('real_amount');
                    const displayAmount = document.getElementById('display_amount');
                    if (realAmount && displayAmount) {
                        realAmount.value = sharedAmount;
                        displayAmount.value = new Intl.NumberFormat('id-ID').format(sharedAmount);
                    }
                }

                const sharedDesc = params.get('shared_description');
                if (sharedDesc) {
                    const descInput = document.querySelector('input[name="description"]');
                    if (descInput) descInput.value = sharedDesc;
                }

                const sharedBadge = document.getElementById('sharedDataBadge');
                if (sharedBadge) sharedBadge.classList.remove('hidden');

                // Bersihkan parameter URL dari browser history agar saat simpan/refresh modal tidak terbuka lagi
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });

        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('PWA Service Worker registered:', reg.scope))
                    .catch((err) => console.log('PWA Service Worker registration failed:', err));
            });
        }

        // PWA Install Prompt Handler
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installBanner = document.getElementById('pwaInstallBanner');
            if (installBanner) {
                installBanner.classList.remove('hidden');
            }
        });

        function triggerPwaInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the PWA install prompt');
                    }
                    deferredPrompt = null;
                    const installBanner = document.getElementById('pwaInstallBanner');
                    if (installBanner) installBanner.classList.add('hidden');
                });
            }
        }
    </script>
</body>
</html>

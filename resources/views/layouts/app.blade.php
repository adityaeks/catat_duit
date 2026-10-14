<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Catat Duit - Kelola Keuangan & Anggaran' }}</title>
    
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
    </style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased flex flex-col justify-start items-center select-none">

    <!-- Mobile Frame Container (Responsive: App width on mobile, sleek centered card on desktop) -->
    <div class="w-full max-w-lg min-h-screen bg-white dark:bg-slate-900 border-x border-slate-200/80 dark:border-slate-800/80 shadow-2xl relative flex flex-col flex-1">
        
        <!-- Flash Message Toast -->
        @if(session('success'))
            <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm bg-emerald-600 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 transition-all duration-300 transform animate-bounce">
                <x-icon name="check" class="w-5 h-5 flex-shrink-0" />
                <span class="text-sm font-semibold flex-1">{{ session('success') }}</span>
                <button type="button" onclick="document.getElementById('flash-toast').remove()" class="text-white/80 hover:text-white">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm bg-rose-600 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 transition-all duration-300 transform animate-bounce">
                <x-icon name="alert-circle" class="w-5 h-5 flex-shrink-0" />
                <span class="text-sm font-semibold flex-1">{{ session('error') }}</span>
                <button type="button" onclick="document.getElementById('flash-toast').remove()" class="text-white/80 hover:text-white">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        @if($errors->any())
            <div id="flash-toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm bg-rose-600 text-white px-4 py-3 rounded-2xl shadow-xl flex flex-col gap-1 transition-all duration-300">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <x-icon name="alert-circle" class="w-4 h-4" />
                    <span>Terjadi kesalahan input:</span>
                </div>
                <ul class="text-xs list-disc list-inside space-y-0.5 opacity-90 pl-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- PWA Install Prompt Banner (Visible only when browser triggers beforeinstallprompt) -->
        <div id="pwaInstallBanner" class="hidden mx-4 mt-3 p-3.5 bg-emerald-700 bg-linear-to-r from-emerald-700 to-teal-700 text-white rounded-2xl shadow-lg border border-emerald-500/30 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                    <img src="/icons/icon-192x192.png" alt="Catat Duit" class="w-7 h-7 rounded-lg">
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold leading-tight truncate">Pasang Aplikasi Catat Duit</p>
                    <p class="text-[10px] text-emerald-100 opacity-90 truncate">Akses cepat langsung dari beranda HP</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <button type="button" onclick="triggerPwaInstall()" class="px-3 py-1.5 bg-white text-emerald-800 hover:bg-emerald-50 text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95">
                    Pasang
                </button>
                <button type="button" onclick="document.getElementById('pwaInstallBanner').classList.add('hidden')" class="p-1 text-white/70 hover:text-white rounded-lg">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 pb-24">
            @yield('content')
        </main>

        <!-- Bottom Navigation Bar (Fixed for Mobile App feel) -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-lg bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800/80 px-4 py-2 z-40">
            <div class="flex items-center justify-between relative">
                
                <!-- Tab Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600' }}">
                    <x-icon name="home" class="w-6 h-6 {{ request()->routeIs('dashboard') ? 'stroke-[2.5]' : '' }}" />
                    <span class="text-[11px]">Beranda</span>
                </a>

                <!-- Tab Transaksi -->
                <a href="{{ route('transactions.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('transactions.*') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600' }}">
                    <x-icon name="receipt" class="w-6 h-6 {{ request()->routeIs('transactions.*') ? 'stroke-[2.5]' : '' }}" />
                    <span class="text-[11px]">Transaksi</span>
                </a>

                <!-- Quick Add Center Button -->
                <div class="relative -top-5">
                    <button type="button" 
                            onclick="openTransactionModal()" 
                            class="w-14 h-14 bg-emerald-600 bg-linear-to-tr from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white rounded-full shadow-lg shadow-emerald-600/30 flex items-center justify-center transition-all transform active:scale-95 focus:outline-none focus:ring-4 focus:ring-emerald-500/20">
                        <x-icon name="plus" class="w-7 h-7 stroke-[2.5]" />
                    </button>
                </div>

                <!-- Tab Anggaran / Budget -->
                <a href="{{ route('budgets.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('budgets.*') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600' }}">
                    <x-icon name="target" class="w-6 h-6 {{ request()->routeIs('budgets.*') ? 'stroke-[2.5]' : '' }}" />
                    <span class="text-[11px]">Anggaran</span>
                </a>

                <!-- Tab Kategori -->
                <a href="{{ route('categories.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition-all {{ request()->routeIs('categories.*') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600' }}">
                    <x-icon name="tags" class="w-6 h-6 {{ request()->routeIs('categories.*') ? 'stroke-[2.5]' : '' }}" />
                    <span class="text-[11px]">Kategori</span>
                </a>

            </div>
        </nav>

        <!-- Global Quick Transaction Modal -->
        <div id="quickTransactionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 border border-slate-100 dark:border-slate-800 transform transition-all animate-in fade-in duration-200 max-h-[92vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <x-icon name="wallet" class="w-5 h-5 text-emerald-600" />
                        <span>Catat Transaksi</span>
                    </h3>
                    <button type="button" onclick="closeTransactionModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Shared Data Auto-Fill Badge -->
                <div id="sharedDataBadge" class="hidden mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2 font-semibold">
                    <x-icon name="sparkles" class="w-4 h-4 text-emerald-600 flex-shrink-0" />
                    <span>Data resi pembayaran terdeteksi & terisi otomatis!</span>
                </div>

                <form action="{{ route('transactions.store') }}" method="POST" class="mt-4 space-y-4" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); btn.innerHTML = '<span>Menyimpan...</span>'; }">
                    @csrf
                    
                    <!-- Segmented Control: Pengeluaran vs Pemasukan -->
                    <div class="grid grid-cols-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="expense" checked class="peer sr-only" onchange="toggleTypeCategories('expense')">
                            <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-sm transition-all flex items-center justify-center gap-1.5">
                                <x-icon name="arrow-down-left" class="w-4 h-4" />
                                <span>Pengeluaran</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="income" class="peer sr-only" onchange="toggleTypeCategories('income')">
                            <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm transition-all flex items-center justify-center gap-1.5">
                                <x-icon name="arrow-up-right" class="w-4 h-4" />
                                <span>Pemasukan</span>
                            </div>
                        </label>
                    </div>

                    <!-- Nominal Input with Rupiah Autoformat -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                            <input type="text" 
                                   id="display_amount" 
                                   inputmode="numeric"
                                   placeholder="0" 
                                   required 
                                   class="w-full pl-14 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-2xl font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                                   oninput="formatRupiahInput(this, 'real_amount')">
                            <input type="hidden" name="amount" id="real_amount" required>
                        </div>
                    </div>

                    <!-- Kategori Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                        <select name="category_id" id="modal_category_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <!-- Populated dynamically / pre-rendered -->
                            @php
                                $allCats = \App\Models\Category::orderBy('name')->get();
                            @endphp
                            @foreach($allCats as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }} ({{ $cat->type == 'expense' ? 'Pengeluaran' : 'Pemasukan' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Transaksi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                        <input type="date" 
                               name="transaction_date" 
                               value="{{ date('Y-m-d') }}" 
                               required 
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Catatan / Keterangan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Catatan (Opsional)</label>
                        <input type="text" 
                               name="description" 
                               placeholder="Contoh: Makan siang bareng tim, Bensin, dll." 
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all text-sm flex items-center justify-center gap-2">
                        <x-icon name="check" class="w-4 h-4" />
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

@extends('layouts.app')

@section('content')
<div class="p-4 space-y-5">

    <!-- Top Navigation Header (Mobile Only) -->
    <div class="flex items-center justify-between pt-1 lg:hidden">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center flex-shrink-0">
                <x-icon name="wallet" class="w-4 h-4 stroke-[2.2]" />
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-tight text-white leading-tight">Catat Duit</h1>
                <p class="text-[11px] text-slate-400 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" 
                    onclick="openReminderModal()" 
                    id="reminderBellBtn"
                    title="Pengingat Jam 5 Sore"
                    class="p-2 surface-card hover:surface-elevated text-slate-400 hover:text-white rounded-xl transition-all relative cursor-pointer">
                <x-icon name="bell" class="w-4 h-4" />
                <span id="reminderActiveBadge" class="hidden absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-[#0B0F19]"></span>
            </button>
            <a href="{{ route('budgets.index') }}" class="px-3 py-1.5 surface-card hover:surface-elevated text-slate-300 hover:text-white rounded-xl text-xs font-medium transition-all flex items-center gap-1.5">
                <x-icon name="target" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Anggaran</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" 
                        onclick="confirmAction(this.form, 'Keluar dari Akun?', 'Apakah Anda yakin ingin keluar dari aplikasi?', 'Ya, Keluar')" 
                        title="Keluar" 
                        class="p-1.5 surface-card hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 rounded-xl transition-all cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Responsive Desktop Grid Container -->
    <div class="lg:grid lg:grid-cols-12 lg:gap-6 lg:space-y-0 space-y-5">
        
        <!-- Left Column: Balance & Budgets (7-8 cols on lg) -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-5">

            <!-- Hero Balance & Financial Ledger Card -->
            <div class="surface-card rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 relative overflow-hidden">
                
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Saldo Bersih</span>
                        <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-md surface-inset text-slate-300">IDR</span>
                    </div>

                    <!-- Date Range Filter Trigger Button -->
                    <button type="button" 
                            onclick="openDashboardDateFilterModal()" 
                            title="Atur Rentang Tanggal"
                            class="group flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/[0.05] hover:bg-emerald-500/15 text-slate-300 hover:text-emerald-300 transition-all border border-white/[0.08] hover:border-emerald-500/30 text-xs font-semibold cursor-pointer">
                        <x-icon name="calendar" class="w-3.5 h-3.5 text-emerald-400 group-hover:scale-110 transition-transform" />
                        <span class="max-w-[130px] sm:max-w-none truncate">{{ $periodLabel }}</span>
                        <x-icon name="chevron-down" class="w-3 h-3 text-slate-400 group-hover:text-emerald-400 transition-colors" />
                    </button>
                </div>

                <div>
                    <div class="text-3xl font-extrabold tracking-tight text-white num-tabular">
                        {{ $periodBalance < 0 ? '-Rp ' . number_format(abs($periodBalance), 0, ',', '.') : 'Rp ' . number_format($periodBalance, 0, ',', '.') }}
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $periodBalance >= 0 ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                            <span>{{ $periodDateRangeText }}</span>
                        </p>
                        @if($preset !== 'this_month')
                            <a href="{{ route('dashboard', ['reset' => 1]) }}" 
                               title="Kembalikan ke Bulan Ini" 
                               class="text-[10px] text-emerald-400 hover:underline flex items-center gap-0.5">
                                <span>(Reset)</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Integrated 3-Column Financial Matrix -->
                <div class="pt-3.5 border-t border-white/[0.08] grid grid-cols-3 gap-2">
                    
                    <!-- Habis Hari Ini -->
                    <div class="surface-inset rounded-2xl p-2.5 overflow-hidden text-left">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block truncate">Hari Ini</span>
                        <p class="text-xs sm:text-sm font-bold text-slate-100 num-tabular truncate mt-0.5">
                            Rp {{ number_format($todayExpense, 0, ',', '.') }}
                        </p>
                        <span class="text-[10px] text-slate-500 block truncate mt-0.5">{{ $todayExpenseCount }} transaksi</span>
                    </div>

                    <!-- Masuk (Periode) -->
                    <div class="surface-inset rounded-2xl p-2.5 overflow-hidden text-left">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block truncate">Masuk</span>
                        <p class="text-xs sm:text-sm font-bold text-emerald-400 num-tabular truncate mt-0.5">
                            +Rp {{ number_format($periodIncome, 0, ',', '.') }}
                        </p>
                        <span class="text-[10px] text-slate-500 block truncate mt-0.5 truncate" title="{{ $periodLabel }}">{{ $periodLabel }}</span>
                    </div>

                    <!-- Keluar (Periode) -->
                    <div class="surface-inset rounded-2xl p-2.5 overflow-hidden text-left">
                        <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block truncate">Keluar</span>
                        <p class="text-xs sm:text-sm font-bold text-rose-400 num-tabular truncate mt-0.5">
                            -Rp {{ number_format($periodExpense, 0, ',', '.') }}
                        </p>
                        <span class="text-[10px] text-slate-500 block truncate mt-0.5 truncate" title="{{ $periodLabel }}">{{ $periodLabel }}</span>
                    </div>

                </div>

            </div>

    <!-- Active Budgets Section -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <x-icon name="target" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Anggaran Berjalan</span>
            </h2>
            <a href="{{ route('budgets.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-0.5">
                <span>Kelola</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </a>
        </div>

        @if($activeBudgets->count() > 0)
            <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3.5">
                @foreach($activeBudgets as $budget)
                    @php
                        $percentage = $budget->percentage;
                        $isDanger = $budget->status === 'danger';
                        $isWarning = $budget->status === 'warning';
                        $barColor = $isDanger ? 'bg-rose-500' : ($isWarning ? 'bg-amber-400' : 'bg-emerald-500');
                        $textColor = $isDanger ? 'text-rose-400' : ($isWarning ? 'text-amber-400' : 'text-emerald-400');
                        $badgeBorder = $isDanger ? 'bg-rose-500/15 text-rose-400 border-rose-500/30' : ($isWarning ? 'bg-amber-400/15 text-amber-400 border-amber-400/30' : 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30');
                    @endphp
                    <div class="p-4 rounded-2xl surface-card space-y-3">
                        
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-sm text-white">{{ $budget->name }}</h3>
                                    @if($budget->category)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-md surface-inset text-slate-300">
                                            {{ $budget->category->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/25">
                                            Semua Pengeluaran
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                                    <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-500" />
                                    <span>{{ $budget->start_date->format('d M') }} – {{ $budget->end_date->format('d M Y') }}</span>
                                    <span class="text-slate-600">•</span>
                                    <span class="text-slate-300 font-medium">Sisa {{ $budget->days_remaining }} hari</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold num-tabular px-2 py-0.5 rounded-lg border {{ $badgeBorder }}">
                                {{ $percentage }}%
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full surface-inset h-2 rounded-full overflow-hidden p-[1px]">
                            <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ min(100, $percentage) }}%"></div>
                        </div>

                        <!-- Stats Row -->
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-white/[0.06] num-tabular">
                            <div>
                                <span class="text-slate-400">Terpakai:</span>
                                <span class="font-bold text-slate-200">Rp {{ number_format($budget->spent_amount, 0, ',', '.') }}</span>
                                <span class="text-slate-500 text-[10px]"> / Rp {{ number_format($budget->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400">Sisa:</span>
                                <span class="font-bold {{ $textColor }}">
                                    Rp {{ number_format($budget->remaining_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Daily recommendation insight & Today's spend -->
                        @if($budget->days_remaining > 0 && $budget->remaining_amount > 0)
                            <div class="px-3 py-2 rounded-xl surface-inset text-[11px] text-slate-400 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" />
                                    <span>Batas harian:</span>
                                    <span class="font-bold text-slate-200 num-tabular">
                                        Rp {{ number_format($budget->daily_remaining_recommendation, 0, ',', '.') }}/hari
                                    </span>
                                </span>
                                <div class="text-right">
                                    <span class="text-slate-500">Hari ini:</span>
                                    <span class="font-bold num-tabular {{ $budget->spent_today > $budget->daily_remaining_recommendation ? 'text-rose-400 font-extrabold' : 'text-slate-200' }}">
                                        Rp {{ number_format($budget->spent_today, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty state for budget -->
            <div class="p-6 rounded-2xl surface-card border-dashed text-center space-y-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 mx-auto flex items-center justify-center">
                    <x-icon name="target" class="w-4 h-4" />
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-200">Belum Ada Anggaran Aktif</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tentukan batas pengeluaran untuk periode tertentu.</p>
                </div>
                <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-bold transition-all shadow-sm">
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                    <span>Buat Anggaran</span>
                </a>
            </div>
        @endif
    </div>
        </div>

        <!-- Right Column: Transactions & Categories (4-5 cols on lg) -->
        <div class="lg:col-span-5 xl:col-span-4 space-y-5">

    <!-- Category Breakdown This Month -->
    @if($expenseCategories->count() > 0)
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <x-icon name="pie-chart" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Pengeluaran Kategori ({{ $periodLabel }})</span>
            </h2>

            <div class="grid {{ $expenseCategories->count() === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-2.5">
                @foreach($expenseCategories->take(4) as $cat)
                    <div class="p-3 surface-card rounded-2xl flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-white" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-4 h-4" />
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-medium text-slate-300 truncate">{{ $cat->name }}</p>
                            <p class="text-xs font-bold text-white num-tabular truncate">Rp {{ number_format($cat->transactions_sum_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transactions List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Transaksi Terbaru</span>
            </h2>
            <a href="{{ route('transactions.index') }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-0.5">
                <span>Lihat Semua</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="surface-card rounded-2xl divide-y divide-white/[0.06] overflow-hidden">
                @foreach($recentTransactions as $tx)
                    <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-white/[0.02] transition-all">
                        
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $tx->category->color ?? '#64748b' }}">
                                <x-icon :name="$tx->category->icon ?? 'tag'" class="w-4 h-4" />
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-slate-200 truncate">
                                    {{ $tx->description ?: $tx->category->name }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                    <span>{{ $tx->category->name }}</span>
                                    <span>•</span>
                                    <span>{{ $tx->transaction_date->translatedFormat('d M') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-bold num-tabular {{ $tx->type == 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $tx->type == 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </p>
                            <span class="text-[9px] text-slate-500 uppercase tracking-wider font-semibold capitalize">{{ $tx->type == 'income' ? 'Masuk' : 'Keluar' }}</span>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 rounded-2xl surface-card border-dashed text-center space-y-2">
                <p class="text-xs font-medium text-slate-400">Belum ada transaksi tercatat.</p>
                <button type="button" onclick="openTransactionModal()" class="px-3 py-1.5 bg-emerald-500 text-slate-950 rounded-xl text-xs font-bold shadow-sm cursor-pointer">
                    + Catat Transaksi
                </button>
            </div>
        @endif
    </div>

        </div>

    </div>

</div>

    <!-- Modal Atur Rentang Tanggal Beranda -->
    <div id="dashboardDateFilterModal" 
         onclick="if(event.target === this) closeDashboardDateFilterModal()"
         class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
        <div class="surface-elevated w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] transform transition-all animate-in fade-in duration-200">
            
            <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 flex items-center justify-center flex-shrink-0">
                        <x-icon name="calendar" class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-tight">Atur Periode Tanggal</h3>
                        <p class="text-[11px] text-slate-400">Pilih rentang waktu saldo di beranda</p>
                    </div>
                </div>
                <button type="button" onclick="closeDashboardDateFilterModal()" class="p-1.5 text-slate-400 hover:text-white rounded-xl hover:bg-white/[0.06] transition-all cursor-pointer">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="mt-4 space-y-4">
                
                <!-- Pilihan Cepat (Presets) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilihan Cepat</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" 
                                onclick="selectDashboardPreset('this_month')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === 'this_month' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>Bulan Ini</span>
                                @if($preset === 'this_month') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                        </button>

                        <button type="button" 
                                onclick="selectDashboardPreset('paycheck')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === 'paycheck' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>Siklus Gajian</span>
                                @if($preset === 'paycheck') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                            <span class="text-[9px] text-slate-400 block font-normal">Tgl 25 - 24</span>
                        </button>

                        <button type="button" 
                                onclick="selectDashboardPreset('7_days')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === '7_days' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>7 Hari</span>
                                @if($preset === '7_days') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                            <span class="text-[9px] text-slate-400 block font-normal">Seminggu</span>
                        </button>

                        <button type="button" 
                                onclick="selectDashboardPreset('30_days')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === '30_days' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>30 Hari</span>
                                @if($preset === '30_days') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                            <span class="text-[9px] text-slate-400 block font-normal">Sebulan</span>
                        </button>

                        <button type="button" 
                                onclick="selectDashboardPreset('last_month')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === 'last_month' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>Bulan Lalu</span>
                                @if($preset === 'last_month') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                        </button>

                        <button type="button" 
                                onclick="selectDashboardPreset('all')"
                                class="p-2.5 rounded-xl text-xs font-semibold transition-all text-left border cursor-pointer {{ $preset === 'all' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40 ring-1 ring-emerald-500/30' : 'surface-card hover:surface-elevated text-slate-300 border-white/[0.06]' }}">
                            <div class="flex items-center justify-between">
                                <span>Semua</span>
                                @if($preset === 'all') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> @endif
                            </div>
                            <span class="text-[9px] text-slate-400 block font-normal">All Time</span>
                        </button>
                    </div>
                </div>

                <!-- Kustom Rentang Tanggal -->
                <div class="p-3.5 surface-inset rounded-2xl space-y-3">
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Atur Tanggal Spesifik</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-1">Dari Tanggal:</span>
                            <input type="date" 
                                   id="dashboardStartDateInput"
                                   name="start_date" 
                                   value="{{ $startDate }}"
                                   class="w-full px-2.5 py-2 surface-card rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 border border-white/[0.08] select-text">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-1">Sampai Tanggal:</span>
                            <input type="date" 
                                   id="dashboardEndDateInput"
                                   name="end_date" 
                                   value="{{ $endDate }}"
                                   class="w-full px-2.5 py-2 surface-card rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 border border-white/[0.08] select-text">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('dashboard', ['reset' => 1]) }}" 
                       class="py-2.5 px-3.5 surface-card hover:surface-elevated text-slate-400 hover:text-white rounded-xl text-xs font-semibold transition-all border border-white/[0.06] text-center">
                        Reset
                    </a>
                    <button type="submit" 
                            class="flex-1 py-2.5 px-4 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-500/20 cursor-pointer">
                        <x-icon name="check" class="w-4 h-4 stroke-[2.5]" />
                        <span>Terapkan Rentang Tanggal</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <script>
        window.openDashboardDateFilterModal = function() {
            const modal = document.getElementById('dashboardDateFilterModal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeDashboardDateFilterModal = function() {
            const modal = document.getElementById('dashboardDateFilterModal');
            if (modal) modal.classList.add('hidden');
        };

        window.selectDashboardPreset = function(presetKey) {
            window.location.href = "{{ route('dashboard') }}?preset=" + presetKey;
        };
    </script>
@endsection

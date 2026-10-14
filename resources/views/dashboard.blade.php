@extends('layouts.app')

@section('content')
<div class="p-4 space-y-6">

    <!-- Top Header -->
    <div class="flex items-center justify-between pt-2">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-600/20">
                <x-icon name="wallet" class="w-5 h-5 stroke-[2.5]" />
            </div>
            <div>
                <h1 class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Catat Duit</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('budgets.index') }}" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-xl transition-all flex items-center gap-1.5 border border-slate-200/60 dark:border-slate-700/60">
                <x-icon name="target" class="w-3.5 h-3.5 text-emerald-600" />
                <span>Anggaran</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" 
                        onclick="confirmAction(this.form, 'Keluar dari Akun?', 'Apakah Anda yakin ingin keluar dari aplikasi?', 'Ya, Keluar')" 
                        title="Keluar" 
                        class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 hover:text-rose-600 text-slate-500 rounded-xl transition-all border border-slate-200/60 dark:border-slate-700/60">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Balance Card -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 bg-linear-to-br from-slate-900 via-slate-800 to-emerald-950 p-6 text-white shadow-xl shadow-slate-900/10 border border-slate-700/50">
        <!-- Background subtle glow -->
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="flex items-center justify-between text-slate-300 text-xs font-semibold tracking-wider uppercase">
            <span>Total Saldo Bersih</span>
            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold border border-emerald-500/30">Global</span>
        </div>

        <div class="mt-2 text-3xl font-black tracking-tight text-white">
            Rp {{ number_format($totalBalance, 0, ',', '.') }}
        </div>

        <!-- Highlight Pengeluaran Hari Ini (Glassmorphic Banner) -->
        <div class="mt-4 p-3 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-sm flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0 border border-rose-500/30">
                    <x-icon name="receipt" class="w-4 h-4 stroke-[2.5]" />
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-slate-300 uppercase tracking-wider font-bold">Habis Hari Ini</span>
                        @if($todayExpense > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                        @endif
                    </div>
                    <p class="text-sm font-extrabold text-white">
                        Rp {{ number_format($todayExpense, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            
            <div class="text-right">
                <span class="text-[10px] text-slate-300 font-medium px-2 py-0.5 rounded-lg bg-black/20 border border-white/5 inline-block">
                    {{ $todayExpenseCount }} transaksi
                </span>
            </div>
        </div>

        <!-- Month to Date Income & Expense Row -->
        <div class="mt-4 pt-3.5 border-t border-slate-700/60 grid grid-cols-2 gap-4">
            
            <!-- Pemasukan Bulan Ini -->
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                    <x-icon name="arrow-down" class="w-4 h-4 stroke-[2.5]" />
                </div>
                <div class="overflow-hidden">
                    <p class="text-[11px] text-slate-400 font-medium">Masuk (Bulan Ini)</p>
                    <p class="text-sm font-bold text-emerald-400 truncate">Rp {{ number_format($thisMonthIncome, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Pengeluaran Bulan Ini -->
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center flex-shrink-0 border border-rose-500/30">
                    <x-icon name="arrow-up" class="w-4 h-4 stroke-[2.5]" />
                </div>
                <div class="overflow-hidden">
                    <p class="text-[11px] text-slate-400 font-medium">Keluar (Bulan Ini)</p>
                    <p class="text-sm font-bold text-rose-400 truncate">Rp {{ number_format($thisMonthExpense, 0, ',', '.') }}</p>
                </div>
            </div>

        </div>
    </div>

    <!-- Active Budgets Section (Highlighting Custom Period Budget) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                <x-icon name="target" class="w-4 h-4 text-emerald-600" />
                <span>Anggaran Aktif Periode Ini</span>
            </h2>
            <a href="{{ route('budgets.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-0.5">
                <span>Kelola</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </a>
        </div>

        @if($activeBudgets->count() > 0)
            <div class="space-y-3">
                @foreach($activeBudgets as $budget)
                    @php
                        $percentage = $budget->percentage;
                        $isDanger = $budget->status === 'danger';
                        $isWarning = $budget->status === 'warning';
                        $barColor = $isDanger ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500');
                        $textColor = $isDanger ? 'text-rose-600 dark:text-rose-400' : ($isWarning ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400');
                        $badgeBg = $isDanger ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 border-rose-200 dark:border-rose-900' : ($isWarning ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 border-amber-200 dark:border-amber-900' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 border-emerald-200 dark:border-emerald-900');
                    @endphp
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 shadow-sm space-y-3">
                        
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $budget->name }}</h3>
                                    @if($budget->category)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            {{ $budget->category->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            Semua Pengeluaran
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <x-icon name="calendar" class="w-3.5 h-3.5" />
                                    <span>{{ $budget->start_date->format('d M') }} – {{ $budget->end_date->format('d M Y') }}</span>
                                    <span class="text-slate-300 dark:text-slate-600">•</span>
                                    <span class="font-medium text-slate-600 dark:text-slate-300">Sisa {{ $budget->days_remaining }} hari</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold px-2 py-1 rounded-xl border {{ $badgeBg }}">
                                {{ $percentage }}%
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                        </div>

                        <!-- Stats Row -->
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-slate-400">Terpakai:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($budget->spent_amount, 0, ',', '.') }}</span>
                                <span class="text-slate-400 text-[10px]"> / Rp {{ number_format($budget->amount, 0, ',', '.') }}</span>
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
                            <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-amber-500" />
                                    <span>Batas aman harian:</span>
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        Rp {{ number_format($budget->daily_remaining_recommendation, 0, ',', '.') }} / hari
                                    </span>
                                </span>
                                <div class="text-right">
                                    <span class="text-slate-400">Hari ini:</span>
                                    <span class="font-bold {{ $budget->spent_today > $budget->daily_remaining_recommendation ? 'text-rose-600 dark:text-rose-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}">
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
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-800/80 border border-dashed border-slate-200 dark:border-slate-700 text-center space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 mx-auto flex items-center justify-center">
                    <x-icon name="target" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Belum Ada Anggaran Aktif</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tentukan batas pengeluaran dari tanggal mulai sampai selesai.</p>
                </div>
                <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-sm transition-all">
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                    <span>Buat Anggaran Sekarang</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Category Breakdown This Month -->
    @if($expenseCategories->count() > 0)
        <div class="space-y-3">
            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                <x-icon name="pie-chart" class="w-4 h-4 text-emerald-600" />
                <span>Pengeluaran Kategori (Bulan Ini)</span>
            </h2>

            <div class="grid {{ $expenseCategories->count() === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-2.5">
                @foreach($expenseCategories->take(4) as $cat)
                    <div class="p-3 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-4 h-4" />
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $cat->name }}</p>
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Rp {{ number_format($cat->transactions_sum_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transactions List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                <x-icon name="clock" class="w-4 h-4 text-emerald-600" />
                <span>Transaksi Terbaru</span>
            </h2>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-0.5">
                <span>Lihat Semua</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 divide-y divide-slate-100 dark:divide-slate-700/60 overflow-hidden shadow-sm">
                @foreach($recentTransactions as $tx)
                    <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-all">
                        
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $tx->category->color ?? '#64748b' }}">
                                <x-icon :name="$tx->category->icon ?? 'tag'" class="w-5 h-5" />
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">
                                    {{ $tx->description ?: $tx->category->name }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                    <span>{{ $tx->category->name }}</span>
                                    <span>•</span>
                                    <span>{{ $tx->transaction_date->translatedFormat('d M') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-extrabold {{ $tx->type == 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $tx->type == 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </p>
                            <span class="text-[10px] text-slate-400 font-medium capitalize">{{ $tx->type == 'income' ? 'Masuk' : 'Keluar' }}</span>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-800/80 border border-dashed border-slate-200 dark:border-slate-700 text-center space-y-2">
                <p class="text-xs font-semibold text-slate-500">Belum ada transaksi.</p>
                <button type="button" onclick="openTransactionModal()" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm">
                    + Catat Transaksi Pertama
                </button>
            </div>
        @endif
    </div>

</div>
@endsection

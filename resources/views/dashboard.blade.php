@extends('layouts.app')

@section('content')
<div class="p-4 space-y-5">

    <!-- Top Navigation Header -->
    <div class="flex items-center justify-between pt-1">
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

    <!-- Hero Balance & Financial Ledger Card -->
    <div class="surface-card rounded-3xl p-5 shadow-xl space-y-4">
        
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Saldo Bersih</span>
            <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded-md surface-inset text-slate-300">IDR</span>
        </div>

        <div>
            <div class="text-3xl font-extrabold tracking-tight text-white num-tabular">
                {{ $totalBalance < 0 ? '-Rp ' . number_format(abs($totalBalance), 0, ',', '.') : 'Rp ' . number_format($totalBalance, 0, ',', '.') }}
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

            <!-- Masuk Bulan Ini -->
            <div class="surface-inset rounded-2xl p-2.5 overflow-hidden text-left">
                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block truncate">Masuk</span>
                <p class="text-xs sm:text-sm font-bold text-emerald-400 num-tabular truncate mt-0.5">
                    +Rp {{ number_format($thisMonthIncome, 0, ',', '.') }}
                </p>
                <span class="text-[10px] text-slate-500 block truncate mt-0.5">Bulan ini</span>
            </div>

            <!-- Keluar Bulan Ini -->
            <div class="surface-inset rounded-2xl p-2.5 overflow-hidden text-left">
                <span class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block truncate">Keluar</span>
                <p class="text-xs sm:text-sm font-bold text-rose-400 num-tabular truncate mt-0.5">
                    -Rp {{ number_format($thisMonthExpense, 0, ',', '.') }}
                </p>
                <span class="text-[10px] text-slate-500 block truncate mt-0.5">Bulan ini</span>
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
            <div class="space-y-3">
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

    <!-- Category Breakdown This Month -->
    @if($expenseCategories->count() > 0)
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <x-icon name="pie-chart" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Pengeluaran Kategori (Bulan Ini)</span>
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
@endsection

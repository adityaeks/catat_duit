@extends('layouts.app')

@section('content')
<div class="p-4 space-y-5">

    <!-- Header & Action Button -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="target" class="w-6 h-6 text-emerald-600" />
                <span>Atur Anggaran</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium">Batas pengeluaran dengan rentang tanggal fleksibel</p>
        </div>
        <button type="button" 
                onclick="openBudgetModal()" 
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 active:scale-95">
            <x-icon name="plus" class="w-4 h-4 stroke-[2.5]" />
            <span>Buat Budget</span>
        </button>
    </div>

    <!-- Active Budgets Summary Card -->
    <div class="p-4 rounded-3xl bg-emerald-800 bg-linear-to-br from-emerald-800 to-teal-900 text-white shadow-lg space-y-3">
        <div class="flex items-center justify-between text-xs text-emerald-200 font-semibold">
            <span>Ringkasan Anggaran Berjalan</span>
            <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px]">{{ $activeBudgets->count() }} Aktif</span>
        </div>
        
        <div class="grid grid-cols-3 gap-2 text-center pt-1">
            <div class="bg-black/10 rounded-2xl p-2.5">
                <p class="text-[10px] text-emerald-200 font-medium">Total Kuota</p>
                <p class="text-xs sm:text-sm font-black truncate mt-0.5">Rp {{ number_format($totalActiveBudgetAmount, 0, ',', '.') }}</p>
            </div>
            <div class="bg-black/10 rounded-2xl p-2.5">
                <p class="text-[10px] text-emerald-200 font-medium">Terpakai</p>
                <p class="text-xs sm:text-sm font-black truncate mt-0.5">Rp {{ number_format($totalActiveBudgetSpent, 0, ',', '.') }}</p>
            </div>
            <div class="bg-black/10 rounded-2xl p-2.5">
                <p class="text-[10px] text-emerald-200 font-medium">Sisa Dana</p>
                <p class="text-xs sm:text-sm font-black truncate mt-0.5 text-emerald-300">Rp {{ number_format($totalActiveBudgetRemaining, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Active Budgets List -->
    <div class="space-y-3">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Anggaran Berjalan (Aktif)</span>
        </h2>

        @if($activeBudgets->count() > 0)
            <div class="space-y-3.5">
                @foreach($activeBudgets as $budget)
                    @php
                        $percentage = $budget->percentage;
                        $isDanger = $budget->status === 'danger';
                        $isWarning = $budget->status === 'warning';
                        $barColor = $isDanger ? 'bg-rose-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500');
                        $textColor = $isDanger ? 'text-rose-600 dark:text-rose-400' : ($isWarning ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400');
                    @endphp
                    <div class="p-4 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm space-y-3 relative group">
                        
                        <!-- Header Row -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $budget->name }}</h3>
                                    @if($budget->category)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $budget->category->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                            Semua Pengeluaran
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    <x-icon name="calendar" class="w-3.5 h-3.5 text-emerald-600" />
                                    <span>{{ $budget->start_date->format('d M') }} – {{ $budget->end_date->format('d M Y') }}</span>
                                    <span>•</span>
                                    <span class="text-slate-700 dark:text-slate-200 font-bold">Sisa {{ $budget->days_remaining }} hari</span>
                                </div>
                            </div>

                            <!-- Actions (Edit/Delete) -->
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        onclick="editBudget({{ json_encode($budget) }})" 
                                        class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                                    <x-icon name="edit-3" class="w-4 h-4" />
                                </button>
                                <form action="{{ route('budgets.destroy', $budget) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="confirmDelete(this.form, 'Hapus Anggaran?', 'Anggaran {{ addslashes($budget->name) }} akan dihapus secara permanen.')" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all">
                                        <x-icon name="trash-2" class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-slate-500">Progress Pemakaian</span>
                                <span class="{{ $textColor }} font-bold">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700/60 h-2.5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                        </div>

                        <!-- Amount breakdown -->
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-slate-400">Terpakai:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($budget->spent_amount, 0, ',', '.') }}</span>
                                <span class="text-slate-400 text-[10px]"> / Rp {{ number_format($budget->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400">Sisa:</span>
                                <span class="font-bold {{ $textColor }}">
                                    Rp {{ number_format($budget->remaining_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Daily recommendation pill -->
                        @if($budget->days_remaining > 0 && $budget->remaining_amount > 0)
                            <div class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 flex items-center justify-between">
                                <span class="flex items-center gap-1.5 font-medium">
                                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-amber-500" />
                                    <span>Batas belanja harian:</span>
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    Rp {{ number_format($budget->daily_remaining_recommendation, 0, ',', '.') }} / hari
                                </span>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-800/80 border border-dashed border-slate-200 dark:border-slate-700 text-center space-y-2">
                <p class="text-xs font-semibold text-slate-500">Tidak ada anggaran yang sedang aktif saat ini.</p>
                <button type="button" onclick="openBudgetModal()" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-sm">
                    + Buat Anggaran Sekarang
                </button>
            </div>
        @endif
    </div>

    <!-- Upcoming & Past Budgets Tabs -->
    @if($upcomingBudgets->count() > 0 || $pastBudgets->count() > 0)
        <div class="pt-4 space-y-4">
            
            <!-- Upcoming -->
            @if($upcomingBudgets->count() > 0)
                <div class="space-y-2">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Akan Datang ({{ $upcomingBudgets->count() }})</h3>
                    <div class="space-y-2">
                        @foreach($upcomingBudgets as $budget)
                            <div class="p-3.5 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $budget->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $budget->start_date->format('d M') }} – {{ $budget->end_date->format('d M Y') }}</p>
                                </div>
                                <div class="text-right flex items-center gap-2">
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($budget->amount, 0, ',', '.') }}</p>
                                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmDelete(this.form, 'Hapus Anggaran?', 'Anggaran {{ addslashes($budget->name) }} akan dihapus secara permanen.')" 
                                                class="p-1 text-slate-400 hover:text-rose-600">
                                            <x-icon name="trash-2" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Past / Expired -->
            @if($pastBudgets->count() > 0)
                <div class="space-y-2">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Riwayat Selesai ({{ $pastBudgets->count() }})</h3>
                    <div class="space-y-2 opacity-75">
                        @foreach($pastBudgets->take(5) as $budget)
                            <div class="p-3.5 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $budget->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $budget->start_date->format('d M') }} – {{ $budget->end_date->format('d M Y') }}</p>
                                </div>
                                <div class="text-right flex items-center gap-2">
                                    <span class="text-xs font-semibold {{ $budget->spent_amount > $budget->amount ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $budget->percentage }}%
                                    </span>
                                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmDelete(this.form, 'Hapus Anggaran?', 'Anggaran {{ addslashes($budget->name) }} akan dihapus secara permanen.')" 
                                                class="p-1 text-slate-400 hover:text-rose-600">
                                            <x-icon name="trash-2" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>

<!-- Modal Create / Edit Budget -->
<div id="budgetModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 border border-slate-100 dark:border-slate-800 max-h-[92vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 id="budgetModalTitle" class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="target" class="w-5 h-5 text-emerald-600" />
                <span>Buat Anggaran Baru</span>
            </h3>
            <button type="button" onclick="closeBudgetModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="budgetForm" action="{{ route('budgets.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div id="budgetFormMethod"></div>

            <!-- Nama Anggaran -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Anggaran</label>
                <input type="text" 
                       name="name" 
                       id="budget_name"
                       required
                       placeholder="Contoh: Belanja Bulanan, Budget Makan, Liburan" 
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Kategori (Opsional: Null = Semua Pengeluaran) -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cakupan Kategori</label>
                <select name="category_id" id="budget_category_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kategori Pengeluaran (Global)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Nominal Target Budget -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Batas Kuota Uang (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                    <input type="text" 
                           id="budget_display_amount" 
                           inputmode="numeric"
                           placeholder="0" 
                           required 
                           class="w-full pl-14 pr-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xl font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           oninput="formatRupiahInput(this, 'budget_real_amount')">
                    <input type="hidden" name="amount" id="budget_real_amount" required>
                </div>
            </div>

            <!-- Quick Preset Date Buttons -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pilihan Cepat Periode</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setPresetRange('this_month')" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-xl border border-slate-200/60 dark:border-slate-700/60 transition-all">
                        Bulan Ini
                    </button>
                    <button type="button" onclick="setPresetRange('payday_cycle')" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-xl border border-slate-200/60 dark:border-slate-700/60 transition-all">
                        Gajian (25–24)
                    </button>
                    <button type="button" onclick="setPresetRange('7_days')" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-xl border border-slate-200/60 dark:border-slate-700/60 transition-all">
                        7 Hari
                    </button>
                </div>
            </div>

            <!-- Rentang Tanggal Mulai s/d Selesai -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Mulai Tgl</label>
                    <input type="date" 
                           name="start_date" 
                           id="budget_start_date"
                           value="{{ date('Y-m-01') }}" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tgl</label>
                    <input type="date" 
                           name="end_date" 
                           id="budget_end_date"
                           value="{{ date('Y-m-t') }}" 
                           required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" 
                          id="budget_notes"
                          rows="2" 
                          placeholder="Alokasi atau tujuan anggaran..." 
                          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all text-sm flex items-center justify-center gap-2">
                <x-icon name="check" class="w-4 h-4" />
                <span id="budgetSubmitText">Simpan Anggaran</span>
            </button>
        </form>

    </div>
</div>

<script>
    function openBudgetModal() {
        document.getElementById('budgetModal').classList.remove('hidden');
        document.getElementById('budgetModalTitle').innerHTML = `
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
            <span>Buat Anggaran Baru</span>
        `;
        document.getElementById('budgetForm').action = "{{ route('budgets.store') }}";
        document.getElementById('budgetFormMethod').innerHTML = '';
        document.getElementById('budget_name').value = '';
        document.getElementById('budget_category_id').value = '';
        document.getElementById('budget_display_amount').value = '';
        document.getElementById('budget_real_amount').value = '';
        document.getElementById('budget_notes').value = '';
        document.getElementById('budgetSubmitText').innerText = 'Simpan Anggaran';
        setPresetRange('this_month');
    }

    function editBudget(budget) {
        document.getElementById('budgetModal').classList.remove('hidden');
        document.getElementById('budgetModalTitle').innerHTML = `
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            <span>Ubah Anggaran</span>
        `;
        document.getElementById('budgetForm').action = `/budgets/${budget.id}`;
        document.getElementById('budgetFormMethod').innerHTML = '@method("PUT")';
        document.getElementById('budget_name').value = budget.name;
        document.getElementById('budget_category_id').value = budget.category_id || '';
        document.getElementById('budget_real_amount').value = budget.amount;
        document.getElementById('budget_display_amount').value = new Intl.NumberFormat('id-ID').format(budget.amount);
        
        const start = budget.start_date.split('T')[0];
        const end = budget.end_date.split('T')[0];
        document.getElementById('budget_start_date').value = start;
        document.getElementById('budget_end_date').value = end;
        document.getElementById('budget_notes').value = budget.notes || '';
        document.getElementById('budgetSubmitText').innerText = 'Perbarui Anggaran';
    }

    function closeBudgetModal() {
        document.getElementById('budgetModal').classList.add('hidden');
    }

    function setPresetRange(type) {
        const today = new Date();
        const y = today.getFullYear();
        const m = today.getMonth();

        let startDate, endDate;

        if (type === 'this_month') {
            startDate = new Date(y, m, 1);
            endDate = new Date(y, m + 1, 0);
        } else if (type === 'payday_cycle') {
            // 25 of this month to 24 of next month (or 25 of prev to 24 of this)
            if (today.getDate() >= 25) {
                startDate = new Date(y, m, 25);
                endDate = new Date(y, m + 1, 24);
            } else {
                startDate = new Date(y, m - 1, 25);
                endDate = new Date(y, m, 24);
            }
        } else if (type === '7_days') {
            startDate = new Date(today);
            endDate = new Date(today);
            endDate.setDate(endDate.getDate() + 6);
        }

        const formatDate = (d) => {
            const month = '' + (d.getMonth() + 1);
            const day = '' + d.getDate();
            return [d.getFullYear(), month.padStart(2, '0'), day.padStart(2, '0')].join('-');
        };

        if (startDate && endDate) {
            document.getElementById('budget_start_date').value = formatDate(startDate);
            document.getElementById('budget_end_date').value = formatDate(endDate);
        }
    }
</script>
@endsection

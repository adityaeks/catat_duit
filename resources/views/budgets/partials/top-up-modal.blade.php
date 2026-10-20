<!-- Modal Tambah Anggaran (Top Up Budget) -->
<div id="topUpModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
    <div class="surface-elevated text-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] max-h-[92vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 flex items-center justify-center">
                    <x-icon name="plus" class="w-4 h-4 stroke-[2.8]" />
                </span>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Tambah Anggaran</h3>
                    <p class="text-[11px] text-slate-400">Tambah alokasi kuota dana berjalan</p>
                </div>
            </div>
            <button type="button" onclick="closeTopUpModal()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="topUpForm" action="" method="POST" class="mt-4 space-y-4" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); btn.innerHTML = '<span>Menyimpan...</span>'; }">
            @csrf

            <!-- Card Info Anggaran Saat Ini -->
            <div class="p-3.5 surface-inset rounded-2xl border border-white/[0.05] space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 id="topup_budget_name" class="font-bold text-sm text-white">Nama Anggaran</h4>
                        <span id="topup_budget_scope" class="text-[10px] font-semibold text-emerald-400">Semua Pengeluaran</span>
                    </div>
                    <span id="topup_budget_days" class="text-xs text-slate-400 font-medium num-tabular">Sisa 0 hari</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/[0.06] text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Kuota Saat Ini:</span>
                        <span id="topup_current_amount_text" class="font-bold text-white num-tabular">Rp 0</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Sisa Dana:</span>
                        <span id="topup_current_remaining_text" class="font-bold text-emerald-400 num-tabular">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Input Nominal Penambahan -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Nominal Penambahan</label>
                    <button type="button" onclick="resetTopUpAmount()" class="text-[11px] text-slate-400 hover:text-rose-400 transition-colors cursor-pointer">Reset</button>
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-400 font-bold text-base sm:text-lg">+ Rp</span>
                    <input type="text" 
                           id="topup_display_amount" 
                           inputmode="numeric"
                           placeholder="0" 
                           required 
                           class="w-full pl-16 pr-4 py-3 surface-inset rounded-2xl text-xl font-bold text-emerald-400 placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 num-tabular select-text transition-all"
                           oninput="onTopUpAmountInput(this)">
                    <input type="hidden" name="add_amount" id="topup_real_amount" required>
                </div>
            </div>

            <!-- Quick Preset Chips -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1.5">Pilihan Cepat Nominal (+)</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="addTopUpPreset(10000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +10.000
                    </button>
                    <button type="button" onclick="addTopUpPreset(20000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +20.000
                    </button>
                    <button type="button" onclick="addTopUpPreset(50000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +50.000
                    </button>
                    <button type="button" onclick="addTopUpPreset(100000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +100.000
                    </button>
                    <button type="button" onclick="addTopUpPreset(200000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +200.000
                    </button>
                    <button type="button" onclick="addTopUpPreset(500000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-emerald-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        +500.000
                    </button>
                </div>
            </div>

            <!-- Live Calculation Preview Card -->
            <div id="topup_preview_card" class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 space-y-2 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-emerald-400 text-[11px] uppercase tracking-wider">
                    <x-icon name="sparkles" class="w-3.5 h-3.5" />
                    <span>Perkiraan Hasil Setelah Ditambah</span>
                </div>
                <div class="space-y-1.5 text-slate-300">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">Total Kuota Baru:</span>
                        <span id="topup_preview_new_amount" class="font-bold text-white num-tabular">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">Sisa Dana Baru:</span>
                        <span id="topup_preview_new_remaining" class="font-bold text-emerald-400 num-tabular">Rp 0</span>
                    </div>
                    <div id="topup_preview_daily_row" class="flex items-center justify-between pt-1 border-t border-emerald-500/15">
                        <span class="text-slate-400 text-[11px]">Batas Belanja Harian Baru:</span>
                        <span id="topup_preview_new_daily" class="font-bold text-amber-300 num-tabular">Rp 0 / hari</span>
                    </div>
                </div>
            </div>

            <!-- Catatan Penambahan -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Catatan Penambahan (Opsional)</label>
                <input type="text" 
                       name="notes" 
                       id="topup_notes" 
                       placeholder="Contoh: Uang saku harian, jatah tambahan, dll." 
                       class="w-full px-4 py-2.5 surface-inset rounded-2xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
            </div>

            <!-- Optional Checkbox: Record as Income Transaction -->
            @php
                $incomeCats = auth()->check() ? auth()->user()->categories()->where('type', 'income')->orderBy('name')->get() : collect();
            @endphp
            <div class="p-3 surface-inset rounded-2xl space-y-2 border border-white/[0.04]">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" 
                           name="record_as_income" 
                           id="topup_record_as_income" 
                           value="1" 
                           onchange="toggleTopupIncomeCat(this.checked)"
                           class="w-4 h-4 rounded text-emerald-500 bg-slate-800 border-slate-600 focus:ring-emerald-500 focus:ring-offset-0">
                    <span class="text-xs font-semibold text-slate-200">Catat juga sebagai Transaksi Pemasukan</span>
                </label>
                <p class="text-[10px] text-slate-400 pl-6.5">
                    Centang jika dana ini berasal dari uang riil hari ini agar saldo keuangan ikut bertambah.
                </p>

                <div id="topup_income_cat_section" class="hidden pl-6.5 pt-1 space-y-1">
                    <label class="block text-[10px] font-semibold text-slate-400">Pilih Kategori Pemasukan</label>
                    <select name="income_category_id" class="w-full px-3 py-2 surface-card rounded-xl text-xs font-semibold text-white focus:outline-none focus:border-emerald-500">
                        @foreach($incomeCats as $ic)
                            <option value="{{ $ic->id }}" class="bg-[#111726] text-white">{{ $ic->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                <x-icon name="plus" class="w-4 h-4 stroke-[3]" />
                <span>Tambah ke Anggaran Sekarang</span>
            </button>
        </form>

    </div>
</div>

<script>
    let currentTopUpBudget = {
        id: null,
        name: '',
        amount: 0,
        remaining: 0,
        days: 0,
        scope: ''
    };

    function openTopUpModal(id, name, amount, remaining, days, scope) {
        currentTopUpBudget = {
            id: id,
            name: name,
            amount: parseFloat(amount) || 0,
            remaining: parseFloat(remaining) || 0,
            days: parseInt(days) || 0,
            scope: scope || 'Semua Pengeluaran'
        };

        const form = document.getElementById('topUpForm');
        if (form) form.action = `/budgets/${id}/top-up`;

        const nameEl = document.getElementById('topup_budget_name');
        if (nameEl) nameEl.innerText = name;

        const scopeEl = document.getElementById('topup_budget_scope');
        if (scopeEl) scopeEl.innerText = currentTopUpBudget.scope;

        const daysEl = document.getElementById('topup_budget_days');
        if (daysEl) daysEl.innerText = currentTopUpBudget.days > 0 ? `Sisa ${currentTopUpBudget.days} hari` : 'Periode berakhir';

        const currAmtEl = document.getElementById('topup_current_amount_text');
        if (currAmtEl) currAmtEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentTopUpBudget.amount);

        const currRemEl = document.getElementById('topup_current_remaining_text');
        if (currRemEl) currRemEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentTopUpBudget.remaining);

        const dispInput = document.getElementById('topup_display_amount');
        if (dispInput) dispInput.value = '';

        const realInput = document.getElementById('topup_real_amount');
        if (realInput) realInput.value = '';

        const notesInput = document.getElementById('topup_notes');
        if (notesInput) notesInput.value = '';

        const recCheck = document.getElementById('topup_record_as_income');
        if (recCheck) {
            recCheck.checked = false;
            toggleTopupIncomeCat(false);
        }

        updateTopUpPreview(0);

        const modal = document.getElementById('topUpModal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                if (dispInput) dispInput.focus();
            }, 100);
        }
    }

    function closeTopUpModal() {
        const modal = document.getElementById('topUpModal');
        if (modal) modal.classList.add('hidden');
    }

    function resetTopUpAmount() {
        const dispInput = document.getElementById('topup_display_amount');
        const realInput = document.getElementById('topup_real_amount');
        if (dispInput) dispInput.value = '';
        if (realInput) realInput.value = '';
        updateTopUpPreview(0);
    }

    function addTopUpPreset(presetVal) {
        const realInput = document.getElementById('topup_real_amount');
        const dispInput = document.getElementById('topup_display_amount');
        let currentVal = parseInt(realInput.value) || 0;
        let newVal = currentVal + presetVal;

        realInput.value = newVal;
        dispInput.value = new Intl.NumberFormat('id-ID').format(newVal);
        updateTopUpPreview(newVal);
    }

    function onTopUpAmountInput(input) {
        let clean = input.value.replace(/[^0-9]/g, '');
        let realVal = clean === '' ? 0 : parseInt(clean);

        const realInput = document.getElementById('topup_real_amount');
        if (realInput) realInput.value = clean;

        input.value = clean === '' ? '' : new Intl.NumberFormat('id-ID').format(clean);
        updateTopUpPreview(realVal);
    }

    function updateTopUpPreview(addAmount) {
        const newAmount = currentTopUpBudget.amount + addAmount;
        const newRemaining = currentTopUpBudget.remaining + addAmount;

        const newAmtEl = document.getElementById('topup_preview_new_amount');
        if (newAmtEl) newAmtEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(newAmount);

        const newRemEl = document.getElementById('topup_preview_new_remaining');
        if (newRemEl) newRemEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(newRemaining);

        const dailyRow = document.getElementById('topup_preview_daily_row');
        const dailyEl = document.getElementById('topup_preview_new_daily');

        if (dailyRow && dailyEl) {
            if (currentTopUpBudget.days > 0) {
                dailyRow.classList.remove('hidden');
                const dailyNew = Math.max(0, Math.round(newRemaining / currentTopUpBudget.days));
                const dailyInc = Math.round(addAmount / currentTopUpBudget.days);
                const incText = addAmount > 0 ? ` (+Rp ${new Intl.NumberFormat('id-ID').format(dailyInc)}/hr)` : '';
                dailyEl.innerText = `Rp ${new Intl.NumberFormat('id-ID').format(dailyNew)} / hari${incText}`;
            } else {
                dailyRow.classList.add('hidden');
            }
        }
    }

    function toggleTopupIncomeCat(checked) {
        const sec = document.getElementById('topup_income_cat_section');
        if (sec) {
            if (checked) {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }
    }
</script>

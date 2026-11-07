<!-- Modal Kurangi Anggaran (Deduct Budget) -->
<div id="deductModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
    <div class="surface-elevated text-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] max-h-[92vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-500/25 text-rose-400 flex items-center justify-center">
                    <x-icon name="minus" class="w-4 h-4 stroke-[2.8]" />
                </span>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Kurangi Anggaran</h3>
                    <p class="text-[11px] text-slate-400">Kurangi alokasi kuota dana berjalan</p>
                </div>
            </div>
            <button type="button" onclick="closeDeductModal()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="deductForm" action="" method="POST" class="mt-4 space-y-4" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); btn.innerHTML = '<span>Menyimpan...</span>'; }">
            @csrf

            <!-- Card Info Anggaran Saat Ini -->
            <div class="p-3.5 surface-inset rounded-2xl border border-white/[0.05] space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 id="deduct_budget_name" class="font-bold text-sm text-white">Nama Anggaran</h4>
                        <span id="deduct_budget_scope" class="text-[10px] font-semibold text-rose-400">Semua Pengeluaran</span>
                    </div>
                    <span id="deduct_budget_days" class="text-xs text-slate-400 font-medium num-tabular">Sisa 0 hari</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/[0.06] text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Kuota Saat Ini:</span>
                        <span id="deduct_current_amount_text" class="font-bold text-white num-tabular">Rp 0</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Sisa Dana:</span>
                        <span id="deduct_current_remaining_text" class="font-bold text-emerald-400 num-tabular">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Input Nominal Pengurangan -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Nominal Pengurangan</label>
                    <button type="button" onclick="resetDeductAmount()" class="text-[11px] text-slate-400 hover:text-rose-400 transition-colors cursor-pointer">Reset</button>
                </div>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-rose-400 font-bold text-base sm:text-lg">- Rp</span>
                    <input type="text" 
                           id="deduct_display_amount" 
                           inputmode="numeric"
                           placeholder="0" 
                           required 
                           class="w-full pl-16 pr-4 py-3 surface-inset rounded-2xl text-xl font-bold text-rose-400 placeholder-slate-600 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 num-tabular select-text transition-all"
                           oninput="onDeductAmountInput(this)">
                    <input type="hidden" name="deduct_amount" id="deduct_real_amount" required>
                </div>
            </div>

            <!-- Quick Preset Chips -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1.5">Pilihan Cepat Nominal (-)</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="addDeductPreset(10000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -10.000
                    </button>
                    <button type="button" onclick="addDeductPreset(20000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -20.000
                    </button>
                    <button type="button" onclick="addDeductPreset(50000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -50.000
                    </button>
                    <button type="button" onclick="addDeductPreset(100000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -100.000
                    </button>
                    <button type="button" onclick="addDeductPreset(200000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -200.000
                    </button>
                    <button type="button" onclick="addDeductPreset(500000)" class="py-2 px-2 surface-card hover:surface-elevated text-slate-200 hover:text-rose-400 text-xs font-bold rounded-xl border border-white/[0.06] active:scale-95 transition-all cursor-pointer">
                        -500.000
                    </button>
                </div>
            </div>

            <!-- Warning Card if exceeds amount -->
            <div id="deduct_warning_card" class="hidden p-3 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-medium space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-rose-400 text-[11px] uppercase tracking-wider">
                    <x-icon name="alert-circle" class="w-4 h-4 flex-shrink-0" />
                    <span>Melebihi Kuota</span>
                </div>
                <p class="text-[11px] text-rose-200/90">Nominal pengurangan tidak boleh melebihi kuota anggaran saat ini.</p>
            </div>

            <!-- Live Calculation Preview Card -->
            <div id="deduct_preview_card" class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 space-y-2 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-rose-400 text-[11px] uppercase tracking-wider">
                    <x-icon name="sparkles" class="w-3.5 h-3.5" />
                    <span>Perkiraan Hasil Setelah Dikurangi</span>
                </div>
                <div class="space-y-1.5 text-slate-300">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">Total Kuota Baru:</span>
                        <span id="deduct_preview_new_amount" class="font-bold text-white num-tabular">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-[11px]">Sisa Dana Baru:</span>
                        <span id="deduct_preview_new_remaining" class="font-bold text-emerald-400 num-tabular">Rp 0</span>
                    </div>
                    <div id="deduct_preview_daily_row" class="flex items-center justify-between pt-1 border-t border-rose-500/15">
                        <span class="text-slate-400 text-[11px]">Batas Belanja Harian Baru:</span>
                        <span id="deduct_preview_new_daily" class="font-bold text-amber-300 num-tabular">Rp 0 / hari</span>
                    </div>
                </div>
            </div>

            <!-- Catatan Pengurangan -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Catatan Pengurangan (Opsional)</label>
                <input type="text" 
                       name="notes" 
                       id="deduct_notes" 
                       placeholder="Contoh: Penyesuaian alokasi, saldo dipindah, dll." 
                       class="w-full px-4 py-2.5 surface-inset rounded-2xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 select-text transition-all">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    id="deduct_submit_button"
                    class="w-full py-3.5 bg-rose-500 hover:bg-rose-400 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-rose-500/20 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                <x-icon name="minus" class="w-4 h-4 stroke-[3]" />
                <span>Kurangi dari Anggaran Sekarang</span>
            </button>
        </form>

    </div>
</div>

<script>
    let currentDeductBudget = {
        id: null,
        name: '',
        amount: 0,
        remaining: 0,
        days: 0,
        scope: ''
    };

    function openDeductModal(id, name, amount, remaining, days, scope) {
        currentDeductBudget = {
            id: id,
            name: name,
            amount: parseFloat(amount) || 0,
            remaining: parseFloat(remaining) || 0,
            days: parseInt(days) || 0,
            scope: scope || 'Semua Pengeluaran'
        };

        const form = document.getElementById('deductForm');
        if (form) form.action = `/budgets/${id}/deduct`;

        const nameEl = document.getElementById('deduct_budget_name');
        if (nameEl) nameEl.innerText = name;

        const scopeEl = document.getElementById('deduct_budget_scope');
        if (scopeEl) scopeEl.innerText = currentDeductBudget.scope;

        const daysEl = document.getElementById('deduct_budget_days');
        if (daysEl) daysEl.innerText = currentDeductBudget.days > 0 ? `Sisa ${currentDeductBudget.days} hari` : 'Periode berakhir';

        const currAmtEl = document.getElementById('deduct_current_amount_text');
        if (currAmtEl) currAmtEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentDeductBudget.amount);

        const currRemEl = document.getElementById('deduct_current_remaining_text');
        if (currRemEl) currRemEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentDeductBudget.remaining);

        const dispInput = document.getElementById('deduct_display_amount');
        if (dispInput) dispInput.value = '';

        const realInput = document.getElementById('deduct_real_amount');
        if (realInput) realInput.value = '';

        const notesInput = document.getElementById('deduct_notes');
        if (notesInput) notesInput.value = '';

        updateDeductPreview(0);

        const modal = document.getElementById('deductModal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                if (dispInput) dispInput.focus();
            }, 100);
        }
    }

    function closeDeductModal() {
        const modal = document.getElementById('deductModal');
        if (modal) modal.classList.add('hidden');
    }

    function resetDeductAmount() {
        const dispInput = document.getElementById('deduct_display_amount');
        const realInput = document.getElementById('deduct_real_amount');
        if (dispInput) dispInput.value = '';
        if (realInput) realInput.value = '';
        updateDeductPreview(0);
    }

    function addDeductPreset(presetVal) {
        const realInput = document.getElementById('deduct_real_amount');
        const dispInput = document.getElementById('deduct_display_amount');
        let currentVal = parseInt(realInput.value) || 0;
        let newVal = currentVal + presetVal;

        // Cap at budget amount if desired or allow user input
        if (newVal > currentDeductBudget.amount) {
            newVal = Math.floor(currentDeductBudget.amount);
        }

        realInput.value = newVal;
        dispInput.value = new Intl.NumberFormat('id-ID').format(newVal);
        updateDeductPreview(newVal);
    }

    function onDeductAmountInput(input) {
        let clean = input.value.replace(/[^0-9]/g, '');
        let realVal = clean === '' ? 0 : parseInt(clean);

        const realInput = document.getElementById('deduct_real_amount');
        if (realInput) realInput.value = clean;

        input.value = clean === '' ? '' : new Intl.NumberFormat('id-ID').format(clean);
        updateDeductPreview(realVal);
    }

    function updateDeductPreview(deductAmount) {
        const exceeds = deductAmount > currentDeductBudget.amount;
        const warningCard = document.getElementById('deduct_warning_card');
        const submitBtn = document.getElementById('deduct_submit_button');

        if (warningCard) {
            if (exceeds) {
                warningCard.classList.remove('hidden');
            } else {
                warningCard.classList.add('hidden');
            }
        }

        if (submitBtn) {
            if (exceeds) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('cursor-pointer');
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('cursor-pointer');
            }
        }

        const newAmount = Math.max(0, currentDeductBudget.amount - deductAmount);
        const newRemaining = currentDeductBudget.remaining - deductAmount;

        const newAmtEl = document.getElementById('deduct_preview_new_amount');
        if (newAmtEl) newAmtEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(newAmount);

        const newRemEl = document.getElementById('deduct_preview_new_remaining');
        if (newRemEl) {
            if (newRemaining < 0) {
                newRemEl.className = 'font-bold text-rose-400 num-tabular';
                newRemEl.innerText = '-Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(newRemaining));
            } else {
                newRemEl.className = 'font-bold text-emerald-400 num-tabular';
                newRemEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(newRemaining);
            }
        }

        const dailyRow = document.getElementById('deduct_preview_daily_row');
        const dailyEl = document.getElementById('deduct_preview_new_daily');

        if (dailyRow && dailyEl) {
            if (currentDeductBudget.days > 0) {
                dailyRow.classList.remove('hidden');
                const dailyNew = Math.max(0, Math.round(newRemaining / currentDeductBudget.days));
                const dailyDec = Math.round(deductAmount / currentDeductBudget.days);
                const decText = deductAmount > 0 ? ` (-Rp ${new Intl.NumberFormat('id-ID').format(dailyDec)}/hr)` : '';
                dailyEl.innerText = `Rp ${new Intl.NumberFormat('id-ID').format(dailyNew)} / hari${decText}`;
            } else {
                dailyRow.classList.add('hidden');
            }
        }
    }
</script>


@extends('layouts.app')

@section('content')
<div class="p-4 space-y-4">

    <!-- Top Header -->
    <div class="flex items-center justify-between pt-1">
        <div>
            <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                <x-icon name="receipt" class="w-4 h-4 text-emerald-400" />
                <span>Riwayat Transaksi</span>
            </h1>
            <p class="text-[11px] text-slate-400 font-medium">Semua catatan pemasukan dan pengeluaran</p>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" 
                    onclick="openTransactionModal(); setTimeout(() => document.getElementById('ocrReceiptInput')?.click(), 150);" 
                    class="px-2.5 py-1.5 surface-card hover:surface-elevated text-emerald-300 hover:text-white rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 active:scale-95 border border-emerald-500/25 cursor-pointer">
                <x-icon name="camera" class="w-3.5 h-3.5 text-emerald-400" />
                <span>Scan</span>
            </button>
            <button type="button" 
                    onclick="openTransactionModal()" 
                    class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 active:scale-95 shadow-sm cursor-pointer">
                <x-icon name="plus" class="w-3.5 h-3.5 stroke-[2.5]" />
                <span>Tambah</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="surface-card rounded-2xl p-3.5 space-y-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="space-y-3">
            
            <!-- Search & Type Filter Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="relative">
                    <x-icon name="search" class="w-3.5 h-3.5 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari transaksi / catatan..." 
                           class="w-full pl-8 pr-3 py-2 surface-inset rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 select-text">
                </div>

                <div class="flex items-center gap-1.5">
                    <select name="type" class="w-full px-2.5 py-2 surface-inset rounded-xl text-xs font-medium text-slate-300 focus:outline-none focus:border-emerald-500 select-text">
                        <option value="" class="bg-[#111726] text-white">Semua Tipe</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }} class="bg-[#111726] text-white">Pengeluaran</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }} class="bg-[#111726] text-white">Pemasukan</option>
                    </select>

                    <select name="category_id" class="w-full px-2.5 py-2 surface-inset rounded-xl text-xs font-medium text-slate-300 focus:outline-none focus:border-emerald-500 select-text">
                        <option value="" class="bg-[#111726] text-white">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }} class="bg-[#111726] text-white">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/[0.06]">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="w-full px-2.5 py-1.5 surface-inset rounded-xl text-xs text-slate-300 focus:outline-none focus:border-emerald-500 select-text">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ request('end_date') }}" 
                           class="w-full px-2.5 py-1.5 surface-inset rounded-xl text-xs text-slate-300 focus:outline-none focus:border-emerald-500 select-text">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center justify-between pt-1">
                <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 hover:text-white font-medium">
                    Reset Filter
                </a>
                <button type="submit" class="px-3.5 py-1.5 surface-elevated hover:bg-[#1D2A47] text-white rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                    <x-icon name="filter" class="w-3.5 h-3.5 text-emerald-400" />
                    <span>Terapkan</span>
                </button>
            </div>

        </form>
    </div>

    <!-- Filter Summary Ledger -->
    <div class="grid grid-cols-3 gap-2 text-center p-3 surface-card rounded-2xl num-tabular">
        <div class="overflow-hidden">
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Total Masuk</p>
            <p class="text-xs font-bold text-emerald-400 truncate mt-0.5">+Rp {{ number_format($totalFilteredIncome, 0, ',', '.') }}</p>
        </div>
        <div class="overflow-hidden">
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Total Keluar</p>
            <p class="text-xs font-bold text-rose-400 truncate mt-0.5">-Rp {{ number_format($totalFilteredExpense, 0, ',', '.') }}</p>
        </div>
        <div class="overflow-hidden">
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Selisih</p>
            <p class="text-xs font-bold {{ $netFiltered >= 0 ? 'text-emerald-400' : 'text-rose-400' }} truncate mt-0.5">
                {{ $netFiltered < 0 ? '-Rp ' . number_format(abs($netFiltered), 0, ',', '.') : '+Rp ' . number_format($netFiltered, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-2.5">
        @if($transactions->count() > 0)
            <div class="surface-card rounded-2xl divide-y divide-white/[0.06] overflow-hidden">
                @foreach($transactions as $tx)
                    <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-white/[0.02] transition-all">
                        
                        <!-- Left Icon & Info -->
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $tx->category->color ?? '#64748b' }}">
                                <x-icon :name="$tx->category->icon ?? 'tag'" class="w-4 h-4" />
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-slate-200 truncate">
                                    {{ $tx->description ?: $tx->category->name }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                    <span class="font-medium text-slate-300">{{ $tx->category->name }}</span>
                                    <span>•</span>
                                    <span>{{ $tx->transaction_date->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Amount & Action buttons -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="text-right">
                                <p class="text-xs font-bold num-tabular {{ $tx->type == 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $tx->type == 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[9px] text-slate-500 font-semibold uppercase tracking-wider capitalize">{{ $tx->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>
                            </div>

                            <div class="flex items-center pl-1">
                                <button type="button" 
                                        onclick="editTransaction({{ json_encode($tx) }})" 
                                        class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer">
                                    <x-icon name="edit-3" class="w-3.5 h-3.5" />
                                </button>
                                <form action="{{ route('transactions.destroy', $tx) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="confirmDelete(this.form, 'Hapus Transaksi?', 'Transaksi senilai Rp {{ number_format($tx->amount, 0, ',', '.') }} akan dihapus.')" 
                                            class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/20 transition-all cursor-pointer">
                                        <x-icon name="trash-2" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="pt-2">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="p-8 rounded-2xl surface-card border-dashed text-center space-y-2">
                <div class="w-9 h-9 rounded-xl bg-white/[0.06] text-slate-400 mx-auto flex items-center justify-center">
                    <x-icon name="receipt" class="w-4 h-4" />
                </div>
                <p class="text-xs font-bold text-slate-300">Tidak ada transaksi ditemukan</p>
                <p class="text-[11px] text-slate-400">Coba ubah kata kunci pencarian atau filter tanggal.</p>
            </div>
        @endif
    </div>

</div>

<!-- Edit Transaction Modal -->
<div id="editTransactionModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4">
    <div class="surface-elevated w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] max-h-[92vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <x-icon name="edit-3" class="w-4 h-4 text-emerald-400" />
                <span>Ubah Transaksi</span>
            </h3>
            <button type="button" onclick="closeEditModal()" class="p-1.5 text-slate-400 hover:text-white rounded-xl hover:bg-white/[0.06] transition-all cursor-pointer">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        <form id="editTxForm" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <!-- Type Selector -->
            <div class="grid grid-cols-2 p-1 surface-inset rounded-xl">
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="edit_type_expense" value="expense" class="peer sr-only" onchange="toggleEditTypeCategories('expense')">
                    <div class="py-2 text-center text-xs font-semibold rounded-lg text-slate-400 peer-checked:bg-rose-500/20 peer-checked:text-rose-300 peer-checked:border peer-checked:border-rose-500/30 transition-all">
                        Pengeluaran
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="edit_type_income" value="income" class="peer sr-only" onchange="toggleEditTypeCategories('income')">
                    <div class="py-2 text-center text-xs font-semibold rounded-lg text-slate-400 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-300 peer-checked:border peer-checked:border-emerald-500/30 transition-all">
                        Pemasukan
                    </div>
                </label>
            </div>

            <!-- Amount Input -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nominal Transaksi</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-base">Rp</span>
                    <input type="text" 
                           id="edit_display_amount" 
                           inputmode="numeric" 
                           required 
                           class="w-full pl-12 pr-4 py-3 surface-inset rounded-xl text-xl font-bold text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 num-tabular select-text transition-all"
                           oninput="formatRupiahInput(this, 'edit_real_amount')">
                    <input type="hidden" name="amount" id="edit_real_amount" required>
                </div>
            </div>

            <!-- Category Selector -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Kategori</label>
                <select name="category_id" id="edit_category_id" required class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:border-emerald-500 select-text">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" class="bg-[#111726] text-white">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tanggal</label>
                <input type="date" 
                       name="transaction_date" 
                       id="edit_transaction_date" 
                       required 
                       class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs font-medium text-slate-200 focus:outline-none focus:border-emerald-500 select-text">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Keterangan / Catatan</label>
                <input type="text" 
                       name="description" 
                       id="edit_description" 
                       class="w-full px-3.5 py-2.5 surface-inset rounded-xl text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500 select-text">
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-xl shadow-md shadow-emerald-500/20 transition-all text-xs flex items-center justify-center gap-1.5 cursor-pointer">
                <x-icon name="check" class="w-4 h-4 stroke-[2.5]" />
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<script>
    function editTransaction(tx) {
        document.getElementById('editTransactionModal').classList.remove('hidden');
        document.getElementById('editTxForm').action = `/transactions/${tx.id}`;
        
        if (tx.type === 'income') {
            document.getElementById('edit_type_income').checked = true;
            toggleEditTypeCategories('income');
        } else {
            document.getElementById('edit_type_expense').checked = true;
            toggleEditTypeCategories('expense');
        }

        document.getElementById('edit_category_id').value = tx.category_id;
        document.getElementById('edit_real_amount').value = tx.amount;
        document.getElementById('edit_display_amount').value = new Intl.NumberFormat('id-ID').format(tx.amount);
        document.getElementById('edit_transaction_date').value = tx.transaction_date.split('T')[0];
        document.getElementById('edit_description').value = tx.description || '';
    }

    function closeEditModal() {
        document.getElementById('editTransactionModal').classList.add('hidden');
    }

    function toggleEditTypeCategories(type) {
        const select = document.getElementById('edit_category_id');
        if (!select) return;
        Array.from(select.options).forEach(option => {
            const optType = option.getAttribute('data-type');
            if (optType === type) {
                option.hidden = false;
                option.disabled = false;
            } else {
                option.hidden = true;
                option.disabled = true;
            }
        });
    }
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="p-4 space-y-4">

    <!-- Top Header -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="receipt" class="w-6 h-6 text-emerald-600" />
                <span>Riwayat Transaksi</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium">Semua catatan pemasukan dan pengeluaran</p>
        </div>
        <button type="button" 
                onclick="openTransactionModal()" 
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 active:scale-95">
            <x-icon name="plus" class="w-4 h-4 stroke-[2.5]" />
            <span>Tambah</span>
        </button>
    </div>

    <!-- Filter & Search Accordion -->
    <div class="bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 p-3.5 shadow-sm space-y-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="space-y-3">
            
            <!-- Search & Type Filter Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari transaksi / catatan..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center gap-1.5">
                    <select name="type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Tipe</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Pemasukan</option>
                    </select>

                    <select name="category_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100 dark:border-slate-700/50">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Dari Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Sampai Tanggal</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ request('end_date') }}" 
                           class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center justify-between pt-1">
                <a href="{{ route('transactions.index') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-semibold">
                    Reset Filter
                </a>
                <button type="submit" class="px-4 py-1.5 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1">
                    <x-icon name="filter" class="w-3.5 h-3.5" />
                    <span>Terapkan Filter</span>
                </button>
            </div>

        </form>
    </div>

    <!-- Filter Summary Bar -->
    <div class="grid grid-cols-3 gap-2 text-center p-3 bg-slate-100 dark:bg-slate-800 rounded-2xl">
        <div>
            <p class="text-[10px] text-slate-400 font-bold uppercase">Total Masuk</p>
            <p class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 truncate mt-0.5">+Rp {{ number_format($totalFilteredIncome, 0, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 font-bold uppercase">Total Keluar</p>
            <p class="text-xs font-extrabold text-rose-600 dark:text-rose-400 truncate mt-0.5">-Rp {{ number_format($totalFilteredExpense, 0, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-[10px] text-slate-400 font-bold uppercase">Selisih</p>
            <p class="text-xs font-extrabold {{ $netFiltered >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} truncate mt-0.5">
                {{ $netFiltered >= 0 ? '+' : '' }}Rp {{ number_format($netFiltered, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="space-y-2.5">
        @if($transactions->count() > 0)
            <div class="bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 divide-y divide-slate-100 dark:divide-slate-700/60 overflow-hidden shadow-sm">
                @foreach($transactions as $tx)
                    <div class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-all">
                        
                        <!-- Left Icon & Info -->
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $tx->category->color ?? '#64748b' }}">
                                <x-icon :name="$tx->category->icon ?? 'tag'" class="w-5 h-5" />
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">
                                    {{ $tx->description ?: $tx->category->name }}
                                </p>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $tx->category->name }}</span>
                                    <span>•</span>
                                    <span>{{ $tx->transaction_date->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Amount & Action buttons -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="text-right">
                                <p class="text-sm font-extrabold {{ $tx->type == 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $tx->type == 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </p>
                                <span class="text-[10px] text-slate-400 font-medium capitalize">{{ $tx->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>
                            </div>

                            <div class="flex items-center pl-1">
                                <button type="button" 
                                        onclick="editTransaction({{ json_encode($tx) }})" 
                                        class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                    <x-icon name="edit-3" class="w-3.5 h-3.5" />
                                </button>
                                <form action="{{ route('transactions.destroy', $tx) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600">
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
            <div class="p-8 rounded-2xl bg-white dark:bg-slate-800/80 border border-dashed border-slate-200 dark:border-slate-700 text-center space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 mx-auto flex items-center justify-center">
                    <x-icon name="receipt" class="w-5 h-5" />
                </div>
                <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Tidak ada transaksi ditemukan</p>
                <p class="text-[11px] text-slate-400">Coba ubah kata kunci pencarian atau filter rentang tanggal.</p>
            </div>
        @endif
    </div>

</div>

<!-- Edit Transaction Modal -->
<div id="editTransactionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 border border-slate-100 dark:border-slate-800 max-h-[92vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="edit-3" class="w-5 h-5 text-emerald-600" />
                <span>Ubah Transaksi</span>
            </h3>
            <button type="button" onclick="closeEditModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="editTxForm" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PUT')

            <!-- Type Selector -->
            <div class="grid grid-cols-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="edit_type_expense" value="expense" class="peer sr-only" onchange="toggleEditTypeCategories('expense')">
                    <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-sm transition-all">
                        Pengeluaran
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="edit_type_income" value="income" class="peer sr-only" onchange="toggleEditTypeCategories('income')">
                    <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm transition-all">
                        Pemasukan
                    </div>
                </label>
            </div>

            <!-- Amount Input -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                    <input type="text" 
                           id="edit_display_amount" 
                           inputmode="numeric"
                           required 
                           class="w-full pl-14 pr-4 py-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-2xl font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           oninput="formatRupiahInput(this, 'edit_real_amount')">
                    <input type="hidden" name="amount" id="edit_real_amount" required>
                </div>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                <select name="category_id" id="edit_category_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
                <input type="date" 
                       name="transaction_date" 
                       id="edit_transaction_date" 
                       required 
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Catatan</label>
                <input type="text" 
                       name="description" 
                       id="edit_description" 
                       placeholder="Catatan transaksi..." 
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all text-sm flex items-center justify-center gap-2">
                <x-icon name="check" class="w-4 h-4" />
                <span>Perbarui Transaksi</span>
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

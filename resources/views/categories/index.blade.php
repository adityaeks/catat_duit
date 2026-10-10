@extends('layouts.app')

@section('content')
<div class="p-4 space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="tags" class="w-6 h-6 text-emerald-600" />
                <span>Kategori Finansial</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium">Kelola kategori pemasukan & pengeluaran</p>
        </div>
        <button type="button" 
                onclick="openCategoryModal()" 
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 active:scale-95">
            <x-icon name="plus" class="w-4 h-4 stroke-[2.5]" />
            <span>Tambah</span>
        </button>
    </div>

    <!-- Expense Categories List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Kategori Pengeluaran ({{ $expenseCategories->count() }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            @foreach($expenseCategories as $cat)
                <div class="p-3.5 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $cat->name }}</p>
                            <p class="text-xs text-slate-400 font-medium">{{ $cat->transactions_count }} transaksi • Total Rp {{ number_format($cat->transactions_sum_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" 
                                onclick="editCategory({{ json_encode($cat) }})" 
                                class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            <x-icon name="edit-3" class="w-4 h-4" />
                        </button>
                        @if($cat->transactions_count == 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all">
                                    <x-icon name="trash-2" class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Income Categories List -->
    <div class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Kategori Pemasukan ({{ $incomeCategories->count() }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            @foreach($incomeCategories as $cat)
                <div class="p-3.5 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $cat->name }}</p>
                            <p class="text-xs text-slate-400 font-medium">{{ $cat->transactions_count }} transaksi • Total Rp {{ number_format($cat->transactions_sum_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" 
                                onclick="editCategory({{ json_encode($cat) }})" 
                                class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            <x-icon name="edit-3" class="w-4 h-4" />
                        </button>
                        @if($cat->transactions_count == 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all">
                                    <x-icon name="trash-2" class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<!-- Modal Create / Edit Category -->
<div id="categoryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-end justify-center sm:items-center p-0 sm:p-4">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 border border-slate-100 dark:border-slate-800 max-h-[92vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 id="categoryModalTitle" class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <x-icon name="tags" class="w-5 h-5 text-emerald-600" />
                <span>Tambah Kategori</span>
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="categoryForm" action="{{ route('categories.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div id="categoryFormMethod"></div>

            <!-- Type -->
            <div class="grid grid-cols-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="cat_type_expense" value="expense" checked class="peer sr-only">
                    <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-sm transition-all">
                        Pengeluaran
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="cat_type_income" value="income" class="peer sr-only">
                    <div class="py-2.5 text-center text-sm font-semibold rounded-xl text-slate-600 dark:text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm transition-all">
                        Pemasukan
                    </div>
                </label>
            </div>

            <!-- Nama Kategori -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Kategori</label>
                <input type="text" 
                       name="name" 
                       id="cat_name" 
                       required 
                       placeholder="Contoh: Belanja Online, Kopi, Investasi" 
                       class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Pilihan Ikon SVG -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pilih Ikon</label>
                <div class="grid grid-cols-6 gap-2 p-2 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                    @php
                        $iconList = [
                            'utensils', 'car', 'shopping-cart', 'receipt', 'gamepad-2', 
                            'heart-pulse', 'graduation-cap', 'home', 'layers', 'briefcase', 
                            'gift', 'laptop', 'trending-up', 'wallet', 'sparkles', 'tag', 'clock', 'info'
                        ];
                    @endphp
                    @foreach($iconList as $ic)
                        <label class="cursor-pointer flex flex-col items-center">
                            <input type="radio" name="icon" value="{{ $ic }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-md transition-all hover:bg-slate-200 dark:hover:bg-slate-700">
                                <x-icon :name="$ic" class="w-5 h-5" />
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Pilihan Warna Kategori -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Warna Aksen</label>
                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $colors = ['#f97316', '#ef4444', '#ec4899', '#8b5cf6', '#6366f1', '#3b82f6', '#06b6d4', '#10b981', '#059669', '#eab308', '#64748b'];
                    @endphp
                    @foreach($colors as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="{{ $color }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-slate-900 dark:peer-checked:border-white peer-checked:scale-110 transition-all flex items-center justify-center shadow-sm" style="background-color: {{ $color }}">
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all text-sm flex items-center justify-center gap-2">
                <x-icon name="check" class="w-4 h-4" />
                <span id="catSubmitText">Simpan Kategori</span>
            </button>
        </form>

    </div>
</div>

<script>
    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModalTitle').innerHTML = `
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L15 21"/><path d="M9 18l6-6-6-6H3v6l6 6Z"/></svg>
            <span>Tambah Kategori</span>
        `;
        document.getElementById('categoryForm').action = "{{ route('categories.store') }}";
        document.getElementById('categoryFormMethod').innerHTML = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_type_expense').checked = true;
        document.getElementById('catSubmitText').innerText = 'Simpan Kategori';
    }

    function editCategory(cat) {
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModalTitle').innerHTML = `
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            <span>Ubah Kategori</span>
        `;
        document.getElementById('categoryForm').action = `/categories/${cat.id}`;
        document.getElementById('categoryFormMethod').innerHTML = '@method("PUT")';
        document.getElementById('cat_name').value = cat.name;

        if (cat.type === 'income') {
            document.getElementById('cat_type_income').checked = true;
        } else {
            document.getElementById('cat_type_expense').checked = true;
        }

        // check icon radio
        const iconRadio = document.querySelector(`input[name="icon"][value="${cat.icon}"]`);
        if (iconRadio) iconRadio.checked = true;

        // check color radio
        const colorRadio = document.querySelector(`input[name="color"][value="${cat.color}"]`);
        if (colorRadio) colorRadio.checked = true;

        document.getElementById('catSubmitText').innerText = 'Perbarui Kategori';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }
</script>
@endsection

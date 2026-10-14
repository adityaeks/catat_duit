@extends('layouts.app')

@section('content')
<div class="p-4 space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <x-icon name="tags" class="w-4 h-4" />
                </span>
                <span>Kategori Finansial</span>
            </h1>
            <p class="text-xs text-slate-400 font-normal mt-0.5">Kelola pos kategori pemasukan & pengeluaran</p>
        </div>
        <button type="button" 
                onclick="openCategoryModal()" 
                class="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
            <x-icon name="plus" class="w-4 h-4 stroke-[2.5]" />
            <span>Tambah</span>
        </button>
    </div>

    <!-- Expense Categories List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                <span>Kategori Pengeluaran ({{ $expenseCategories->count() }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            @foreach($expenseCategories as $cat)
                <div class="p-3.5 surface-card surface-card-hover rounded-2xl flex items-center justify-between gap-3 shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-inner" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white tracking-tight">{{ $cat->name }}</p>
                            <p class="text-xs text-slate-400 font-normal num-tabular">{{ $cat->transactions_count }} transaksi • Total Rp {{ number_format($cat->transactions_sum_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" 
                                onclick="editCategory({{ json_encode($cat) }})" 
                                class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer"
                                title="Ubah Kategori">
                            <x-icon name="edit-3" class="w-4 h-4" />
                        </button>
                        @if($cat->transactions_count == 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        onclick="confirmDelete(this.form, 'Hapus Kategori?', 'Kategori {{ addslashes($cat->name) }} akan dihapus.')" 
                                        class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer"
                                        title="Hapus Kategori">
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
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Kategori Pemasukan ({{ $incomeCategories->count() }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-2.5">
            @foreach($incomeCategories as $cat)
                <div class="p-3.5 surface-card surface-card-hover rounded-2xl flex items-center justify-between gap-3 shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-inner" style="background-color: {{ $cat->color }}">
                            <x-icon :name="$cat->icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white tracking-tight">{{ $cat->name }}</p>
                            <p class="text-xs text-slate-400 font-normal num-tabular">{{ $cat->transactions_count }} transaksi • Total Rp {{ number_format($cat->transactions_sum_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button" 
                                onclick="editCategory({{ json_encode($cat) }})" 
                                class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer"
                                title="Ubah Kategori">
                            <x-icon name="edit-3" class="w-4 h-4" />
                        </button>
                        @if($cat->transactions_count == 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        onclick="confirmDelete(this.form, 'Hapus Kategori?', 'Kategori {{ addslashes($cat->name) }} akan dihapus.')" 
                                        class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-rose-500/10 transition-all cursor-pointer"
                                        title="Hapus Kategori">
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
<div id="categoryModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-md hidden flex items-end justify-center sm:items-center p-0 sm:p-4">
    <div class="surface-elevated text-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 border border-white/[0.1] max-h-[92vh] overflow-y-auto">
        
        <div class="flex items-center justify-between pb-4 border-b border-white/[0.08]">
            <h3 id="categoryModalTitle" class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <x-icon name="tags" class="w-4 h-4" />
                </span>
                <span>Tambah Kategori</span>
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <form id="categoryForm" action="{{ route('categories.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div id="categoryFormMethod"></div>

            <!-- Type -->
            <div class="grid grid-cols-2 p-1 surface-inset rounded-2xl">
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="cat_type_expense" value="expense" checked class="peer sr-only">
                    <div class="py-2.5 text-center text-xs font-semibold rounded-xl text-slate-400 peer-checked:bg-rose-500/20 peer-checked:text-rose-300 peer-checked:border peer-checked:border-rose-500/30 transition-all">
                        Pengeluaran
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" id="cat_type_income" value="income" class="peer sr-only">
                    <div class="py-2.5 text-center text-xs font-semibold rounded-xl text-slate-400 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-300 peer-checked:border peer-checked:border-emerald-500/30 transition-all">
                        Pemasukan
                    </div>
                </label>
            </div>

            <!-- Nama Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nama Kategori</label>
                <input type="text" 
                       name="name" 
                       id="cat_name" 
                       required 
                       placeholder="Contoh: Belanja Online, Kopi, Investasi" 
                       class="w-full px-4 py-3 surface-inset rounded-2xl text-sm font-semibold text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 select-text transition-all">
            </div>

            <!-- Pilihan Ikon SVG -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Pilih Ikon</label>
                <div class="grid grid-cols-6 gap-2 p-2.5 surface-inset rounded-2xl">
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
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-slate-950 transition-all hover:bg-white/[0.06]">
                                <x-icon :name="$ic" class="w-5 h-5" />
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Pilihan Warna Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Warna Aksen</label>
                <div class="flex items-center gap-2.5 flex-wrap p-2.5 surface-inset rounded-2xl">
                    @php
                        $colors = ['#f97316', '#ef4444', '#ec4899', '#8b5cf6', '#6366f1', '#3b82f6', '#06b6d4', '#10b981', '#059669', '#eab308', '#64748b'];
                    @endphp
                    @foreach($colors as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="{{ $color }}" {{ $loop->first ? 'checked' : '' }} class="peer sr-only">
                            <div class="w-7 h-7 rounded-full border-2 border-transparent peer-checked:border-white peer-checked:scale-115 transition-all flex items-center justify-center shadow-md ring-2 ring-transparent peer-checked:ring-emerald-500/50" style="background-color: {{ $color }}">
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-400 active:scale-[0.98] text-slate-950 font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                <x-icon name="check" class="w-4 h-4 stroke-[2.5]" />
                <span id="catSubmitText">Simpan Kategori</span>
            </button>
        </form>

    </div>
</div>

<script>
    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModalTitle').innerHTML = `
            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L15 21"/><path d="M9 18l6-6-6-6H3v6l6 6Z"/></svg>
            </span>
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
            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </span>
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

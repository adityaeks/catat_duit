<!-- Modal Fitur Couple (Keuangan Pasangan) -->
<div id="coupleModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md hidden flex items-end justify-center sm:items-center p-0 sm:p-4 transition-all">
    <div class="surface-elevated text-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-white/[0.1] max-h-[92vh] overflow-y-auto">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3.5 border-b border-white/[0.08]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-pink-500/15 border border-pink-500/25 text-pink-400 flex items-center justify-center text-sm shadow-sm">
                    💕
                </span>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Keuangan Couple</h3>
                    <p class="text-[11px] text-slate-400">Hubungkan akun & kelola keuangan berdua</p>
                </div>
            </div>
            <button type="button" onclick="closeCoupleModal()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.06] transition-all cursor-pointer">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        @php
            $authUser = auth()->user();
            $isCoupled = $authUser ? $authUser->isCoupled() : false;
            $partner = $isCoupled ? $authUser->partner() : null;
        @endphp

        <div class="mt-4 space-y-4">

            @if($isCoupled && $partner)
                <!-- TAMPILAN JIKA SUDAH TERHUBUNG DENGAN PASANGAN -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-pink-500/10 via-rose-500/10 to-transparent border border-pink-500/20 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-pink-500/20 border border-pink-500/30 flex items-center justify-center mx-auto text-xl shadow-lg shadow-pink-500/10">
                        👩‍❤️‍👨
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-[10px] font-bold">
                            Terkoneksi Aktif
                        </span>
                        <h4 class="text-base font-bold text-white mt-1">{{ $partner->name }}</h4>
                        <p class="text-xs text-slate-400 num-tabular">{{ $partner->email }}</p>
                        @if($authUser->couple?->connected_at)
                            <p class="text-[10px] text-pink-300 mt-1">
                                Terhubung sejak {{ $authUser->couple->connected_at->translatedFormat('d F Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="p-3.5 surface-inset rounded-2xl text-xs text-slate-300 space-y-2 border border-white/[0.04]">
                    <div class="flex items-center gap-2 font-semibold text-white text-[11px]">
                        <x-icon name="info" class="w-3.5 h-3.5 text-pink-400" />
                        <span>Keuntungan Mode Couple:</span>
                    </div>
                    <ul class="text-[11px] text-slate-400 space-y-1.5 pl-4 list-disc">
                        <li>Bisa memantau total pengeluaran couple hari ini secara real-time.</li>
                        <li>Bisa melihat gabungan saldo dan riwayat transaksi bersama.</li>
                        <li>Bisa membuat anggaran bersama (*Couple Budget*).</li>
                    </ul>
                </div>

                <!-- Form Putus Hubungan -->
                <form action="{{ route('couple.disconnect') }}" method="POST" id="formDisconnectCouple">
                    @csrf
                    <button type="button" 
                            onclick="confirmAction(this.form, 'Putuskan Sambungan Couple?', 'Data keuangan Anda dan pasangan tidak lagi tergabung di mode Couple.', 'Ya, Putuskan')" 
                            class="w-full py-2.5 px-4 surface-card hover:bg-rose-500/15 text-slate-400 hover:text-rose-400 border border-white/[0.08] hover:border-rose-500/30 font-semibold rounded-2xl transition-all text-xs cursor-pointer">
                        Putuskan Sambungan Pasangan
                    </button>
                </form>

            @else
                <!-- TAMPILAN JIKA BELUM TERHUBUNG -->

                <!-- Card 1: Kode Pasangan Saya -->
                <div class="p-4 surface-inset rounded-2xl border border-white/[0.06] space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Pasangan Anda</span>
                        <span class="text-[10px] text-pink-400 font-semibold">Bagikan ke Pasangan</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-2.5 bg-black/40 rounded-xl border border-white/[0.08]">
                        <span id="myPairingCodeText" class="font-mono font-extrabold text-base sm:text-lg text-pink-300 tracking-wider">
                            {{ $authUser?->getPairingCode() ?? 'CP-XXXXXX' }}
                        </span>
                        <button type="button" 
                                onclick="copyPairingCode()" 
                                id="btnCopyPairingCode"
                                class="px-3 py-1.5 surface-elevated hover:bg-[#1D2A47] text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                            <span>Salin</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-relaxed">
                        Kirimkan kode ini ke pasangan Anda, atau mintalah kode milik pasangan Anda untuk dimasukkan di bawah.
                    </p>
                </div>

                <!-- Card 2: Form Masukkan Kode Pasangan -->
                <form action="{{ route('couple.connect') }}" method="POST" class="space-y-3" onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); btn.innerHTML = '<span>Menghubungkan...</span>'; }">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Masukkan Kode Pasangan
                        </label>
                        <input type="text" 
                               name="pairing_code" 
                               id="input_pairing_code"
                               required 
                               placeholder="Contoh: CP-8F29AB" 
                               class="w-full px-4 py-3 surface-inset rounded-2xl text-sm font-mono font-bold uppercase tracking-wider text-pink-300 placeholder-slate-600 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 select-text transition-all">
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-400 hover:to-rose-400 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-pink-500/20 transition-all text-xs flex items-center justify-center gap-2 cursor-pointer">
                        <span>Hubungkan Akun Pasangan 💕</span>
                    </button>
                </form>

            @endif

        </div>

    </div>
</div>

<script>
    function openCoupleModal() {
        const modal = document.getElementById('coupleModal');
        if (modal) {
            modal.classList.remove('hidden');
            const input = document.getElementById('input_pairing_code');
            if (input) {
                setTimeout(() => input.focus(), 150);
            }
        }
    }

    function closeCoupleModal() {
        const modal = document.getElementById('coupleModal');
        if (modal) modal.classList.add('hidden');
    }

    function copyPairingCode() {
        const codeEl = document.getElementById('myPairingCodeText');
        if (!codeEl) return;
        const code = codeEl.innerText.trim();

        navigator.clipboard.writeText(code).then(() => {
            const btn = document.getElementById('btnCopyPairingCode');
            if (btn) {
                const oldText = btn.innerHTML;
                btn.innerHTML = '<span>Tersalin! ✓</span>';
                btn.classList.add('bg-emerald-500/20', 'text-emerald-300', 'border', 'border-emerald-500/30');
                setTimeout(() => {
                    btn.innerHTML = oldText;
                    btn.classList.remove('bg-emerald-500/20', 'text-emerald-300', 'border', 'border-emerald-500/30');
                }, 2000);
            }
        }).catch(err => {
            alert('Kode: ' + code);
        });
    }
</script>


@if($totalFilteredExpense > 0)
<div class="surface-card rounded-2xl p-4 space-y-4 transition-all">
    
    <!-- Header: Title & Switcher -->
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                <x-icon name="pie-chart" class="w-4 h-4" />
            </span>
            <div>
                <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                    <span>Grafik Pengeluaran per Kategori</span>
                </h2>
                <p class="text-[10px] text-slate-400">
                    Total: <span class="font-bold text-rose-400 num-tabular">Rp {{ number_format($totalFilteredExpense, 0, ',', '.') }}</span>
                </p>
            </div>
        </div>

        <!-- Segmented Tab Controls: Circle Kategori vs Tren Waktu -->
        <div class="flex items-center p-0.5 surface-inset rounded-xl border border-white/[0.04]">
            <button type="button" 
                    id="btnChartTabCategory" 
                    onclick="switchChartTab('category')" 
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all bg-rose-500/20 text-rose-300 border border-rose-500/30 cursor-pointer flex items-center gap-1">
                <x-icon name="pie-chart" class="w-3 h-3" />
                <span>Circle Kategori</span>
            </button>
            <button type="button" 
                    id="btnChartTabDaily" 
                    onclick="switchChartTab('daily')" 
                    class="px-2.5 py-1 rounded-lg text-[10px] font-semibold transition-all text-slate-400 hover:text-slate-200 cursor-pointer flex items-center gap-1">
                <x-icon name="trending-down" class="w-3 h-3" />
                <span>Tren Waktu</span>
            </button>
        </div>
    </div>

    <!-- VIEW 1: Circle Donut Chart per Kategori (Default Active) -->
    <div id="chartViewCategory" class="space-y-4">
        
        <div class="flex flex-col sm:flex-row items-center sm:items-start justify-center gap-5 sm:gap-7 pt-1">
            
            <!-- SVG Donut Chart with Center Amount -->
            <div class="w-44 h-44 sm:w-48 sm:h-48 relative flex items-center justify-center flex-shrink-0 select-none">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 160 160">
                    <!-- Background Circle Ring -->
                    <circle cx="80" cy="80" r="58" 
                            fill="none" 
                            stroke="rgba(255, 255, 255, 0.05)" 
                            stroke-width="18">
                    </circle>
                    <!-- Category Colored Slices -->
                    @foreach($expenseChartCategories as $cat)
                        <circle cx="80" cy="80" r="58"
                                fill="none"
                                stroke="{{ $cat['color'] }}"
                                stroke-width="18"
                                stroke-dasharray="{{ $cat['stroke_dasharray'] }}"
                                stroke-dashoffset="{{ $cat['stroke_dashoffset'] }}"
                                class="transition-all duration-300 hover:opacity-80 cursor-pointer"
                                onmouseenter="hoverCircleCategory('{{ addslashes($cat['name']) }}', '{{ number_format($cat['total'], 0, ',', '.') }}', '{{ $cat['percentage'] }}%')"
                                onmouseleave="resetCircleCategory()">
                        </circle>
                    @endforeach
                </svg>

                <!-- Center Text Overlay -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-3 pointer-events-none select-none">
                    <span id="circleCenterLabel" class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider truncate max-w-[100px] transition-all">
                        Total Keluar
                    </span>
                    <span id="circleCenterAmount" class="text-xs sm:text-sm font-extrabold text-white num-tabular tracking-tight transition-all mt-0.5">
                        Rp {{ number_format($totalFilteredExpense, 0, ',', '.') }}
                    </span>
                    <span id="circleCenterBadge" class="hidden text-[9px] font-bold text-rose-400 num-tabular mt-0.5 px-1.5 py-0.5 rounded-full bg-rose-500/15 border border-rose-500/25">
                        100%
                    </span>
                </div>
            </div>

            <!-- Category Breakdown List (Right side on desktop, Below on mobile) -->
            <div class="w-full flex-1 space-y-2">
                @foreach($expenseChartCategories as $cat)
                    <div class="p-2.5 surface-inset hover:bg-white/[0.04] rounded-xl flex items-center justify-between gap-3 transition-all cursor-pointer group"
                         onmouseenter="hoverCircleCategory('{{ addslashes($cat['name']) }}', '{{ number_format($cat['total'], 0, ',', '.') }}', '{{ $cat['percentage'] }}%')"
                         onmouseleave="resetCircleCategory()">
                        <div class="flex items-center gap-2.5 overflow-hidden flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-sm transition-transform group-hover:scale-105" 
                                 style="background-color: {{ $cat['color'] }}">
                                <x-icon :name="$cat['icon']" class="w-4 h-4" />
                            </div>
                            <div class="overflow-hidden flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-semibold text-slate-200 truncate group-hover:text-white">{{ $cat['name'] }}</p>
                                    <span class="text-xs font-bold text-rose-400 num-tabular flex-shrink-0">
                                        Rp {{ number_format($cat['total'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-full h-1.5 surface-canvas rounded-full overflow-hidden flex-1">
                                        <div class="h-full rounded-full transition-all duration-500" 
                                             style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}">
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-semibold num-tabular flex-shrink-0">
                                        {{ $cat['percentage'] }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

    <!-- VIEW 2: Tren Waktu (Daily Trend Bar Chart) -->
    <div id="chartViewDaily" class="hidden space-y-3">
        <!-- Quick Insight Badges -->
        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-white/[0.04]">
            @if($expenseChartPeakDay)
                <div class="flex items-center gap-1">
                    <span class="text-slate-500">Tertinggi:</span>
                    <span class="font-bold text-amber-300 num-tabular">Rp {{ number_format($expenseChartPeakDay['amount'], 0, ',', '.') }}</span>
                    <span class="text-slate-500 text-[9px]">({{ $expenseChartPeakDay['short_label'] }})</span>
                </div>
            @endif
            <div class="flex items-center gap-1 ml-auto">
                <span class="text-slate-500">Rata-rata:</span>
                <span class="font-bold text-slate-200 num-tabular">Rp {{ number_format($expenseChartDailyAvg, 0, ',', '.') }}/hari</span>
            </div>
        </div>

        <!-- The Bar Chart Container -->
        <div class="relative pt-6">
            <!-- Gridlines Background -->
            <div class="absolute inset-x-0 top-6 bottom-7 flex flex-col justify-between pointer-events-none opacity-20">
                <div class="border-b border-dashed border-slate-400 w-full"></div>
                <div class="border-b border-dashed border-slate-400 w-full"></div>
                <div class="border-b border-slate-400 w-full"></div>
            </div>

            <!-- Bars wrapper (scrollable on mobile if many bars) -->
            <div class="h-40 flex items-end justify-between gap-1.5 sm:gap-2 overflow-x-auto pb-1 px-1">
                @foreach($expenseChartDaily as $item)
                    @php
                        $pct = $expenseChartMaxDaily > 0 ? ($item['amount'] / $expenseChartMaxDaily) * 100 : 0;
                        $isPeak = $item['amount'] > 0 && $item['amount'] === $expenseChartMaxDaily;
                        $hasAmount = $item['amount'] > 0;
                    @endphp
                    <div class="flex-1 min-w-[28px] sm:min-w-[34px] max-w-[48px] h-full flex flex-col justify-end items-center group relative cursor-pointer">
                        <!-- Floating Tooltip on Hover/Touch -->
                        <div class="absolute -top-7 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-30 whitespace-nowrap bg-slate-900 border border-white/10 text-white text-[10px] font-bold py-1 px-2 rounded-lg shadow-2xl num-tabular scale-90 group-hover:scale-100">
                            {{ $item['short_label'] }}: Rp {{ number_format($item['amount'], 0, ',', '.') }}
                        </div>

                        <!-- Bar Pillar -->
                        <div class="w-full flex flex-col justify-end items-center h-full">
                            <div class="w-full rounded-t-md transition-all duration-300 {{ $isPeak ? 'bg-gradient-to-t from-rose-500 to-amber-400 shadow-sm shadow-amber-500/20' : ($hasAmount ? 'bg-rose-500/75 group-hover:bg-rose-400' : 'bg-white/[0.04]') }}" 
                                 style="height: {{ max(4, round($pct)) }}%">
                            </div>
                        </div>

                        <!-- X-axis Date Label -->
                        <div class="w-full text-center mt-1.5 flex flex-col items-center">
                            <span class="text-[9px] font-bold {{ $hasAmount ? 'text-slate-300' : 'text-slate-600' }} group-hover:text-white num-tabular truncate w-full">
                                {{ explode(' ', $item['short_label'])[0] }}
                            </span>
                            <span class="text-[8px] text-slate-500 group-hover:text-slate-400 uppercase tracking-tighter">
                                {{ $item['day_name'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<script>
    const defaultCenterLabel = 'Total Keluar';
    const defaultCenterAmount = 'Rp {{ number_format($totalFilteredExpense, 0, ',', '.') }}';

    function hoverCircleCategory(name, amount, percent) {
        const lbl = document.getElementById('circleCenterLabel');
        const amt = document.getElementById('circleCenterAmount');
        const badge = document.getElementById('circleCenterBadge');

        if (lbl) lbl.innerText = name;
        if (amt) amt.innerText = 'Rp ' + amount;
        if (badge) {
            badge.innerText = percent;
            badge.classList.remove('hidden');
        }
    }

    function resetCircleCategory() {
        const lbl = document.getElementById('circleCenterLabel');
        const amt = document.getElementById('circleCenterAmount');
        const badge = document.getElementById('circleCenterBadge');

        if (lbl) lbl.innerText = defaultCenterLabel;
        if (amt) amt.innerText = defaultCenterAmount;
        if (badge) badge.classList.add('hidden');
    }

    function switchChartTab(tab) {
        const dailyView = document.getElementById('chartViewDaily');
        const catView = document.getElementById('chartViewCategory');
        const btnDaily = document.getElementById('btnChartTabDaily');
        const btnCat = document.getElementById('btnChartTabCategory');

        if (tab === 'daily') {
            dailyView?.classList.remove('hidden');
            catView?.classList.add('hidden');

            btnDaily.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all bg-rose-500/20 text-rose-300 border border-rose-500/30 cursor-pointer flex items-center gap-1';
            btnCat.className = 'px-2.5 py-1 rounded-lg text-[10px] font-semibold transition-all text-slate-400 hover:text-slate-200 cursor-pointer flex items-center gap-1';
        } else {
            dailyView?.classList.add('hidden');
            catView?.classList.remove('hidden');

            btnCat.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all bg-rose-500/20 text-rose-300 border border-rose-500/30 cursor-pointer flex items-center gap-1';
            btnDaily.className = 'px-2.5 py-1 rounded-lg text-[10px] font-semibold transition-all text-slate-400 hover:text-slate-200 cursor-pointer flex items-center gap-1';
        }
    }
</script>
@endif

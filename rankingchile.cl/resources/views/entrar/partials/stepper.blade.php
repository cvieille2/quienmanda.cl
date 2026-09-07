<div class="grid gap-2 sm:grid-cols-5">
    @foreach ($steps as $index => $stepItem)
        <div
            class="flex items-start gap-3 rounded-2xl border px-3 py-3 text-left transition"
            :class="step === {{ $index + 1 }} ? 'border-[#F53003] bg-[#FFF4F1] shadow-sm' : (step > {{ $index + 1 }} ? 'border-[#F8B803] bg-[#FFFCEB]' : 'border-gray-200 bg-white')"
            aria-current="{{ $index + 1 }}"
        >
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black"
                  :class="step === {{ $index + 1 }} ? 'bg-[#F53003] text-white' : (step > {{ $index + 1 }} ? 'bg-[#F8B803] text-[#1B1B18]' : 'bg-gray-100 text-gray-600')">
                {{ $index + 1 }}/{{ count($steps) }}
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-extrabold text-[#1B1B18]">{{ $stepItem['title'] }}</span>
                <span class="mt-0.5 block text-[11px] leading-tight text-gray-500">{{ $stepItem['hint'] }}</span>
            </span>
        </div>
    @endforeach
</div>

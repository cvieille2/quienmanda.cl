@php
    $categoryFilters = $categoryFilters ?? collect();
    $periodFilters = $periodFilters ?? collect();
@endphp

<div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide pb-1 lg:flex-wrap lg:overflow-visible">
            @foreach ($categoryFilters as $filter)
                <a href="{{ $filter['url'] }}"
                   class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition border {{ $filter['active'] ? 'border-[#07182D] bg-[#07182D] text-white' : 'border-transparent bg-[#EEF2F7] text-[#172033] hover:bg-[#E3E9F1]' }}"
                   aria-current="{{ $filter['active'] ? 'page' : 'false' }}">
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="w-full lg:w-auto">
        <div class="rounded-xl bg-[#EEF2F7] p-1 flex items-center gap-1">
            @foreach ($periodFilters as $filter)
                <a href="{{ $filter['url'] }}"
                   class="flex-1 lg:flex-none rounded-lg px-4 py-2 text-sm font-semibold text-center transition {{ $filter['active'] ? 'bg-white text-[#07182D] shadow-[0_1px_4px_rgba(0,0,0,0.08)]' : 'text-[#657184] hover:text-[#07182D]' }}"
                   aria-current="{{ $filter['active'] ? 'page' : 'false' }}">
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>

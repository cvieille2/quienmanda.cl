@php
    use App\Enums\RankingPeriodWindow;

    $current = $currentPeriod ?? RankingPeriodWindow::WEEK;
    $baseUrl = $baseUrl ?? url()->current();
    $extraQuery = request()->except('period');
    $periods = [
        RankingPeriodWindow::TODAY,
        RankingPeriodWindow::WEEK,
        RankingPeriodWindow::MONTH,
        RankingPeriodWindow::YEAR,
    ];
@endphp

<div class="mx-auto max-w-6xl px-4 pt-5">
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($periods as $period)
            <a href="{{ $baseUrl . '?' . http_build_query(array_merge($extraQuery, ['period' => $period->value])) }}"
               class="rounded-full border px-4 py-2 text-sm font-bold transition {{ $current === $period ? 'border-[#F53003] bg-[#F53003] text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-900' }}">
                {{ $period->label() }}
            </a>
        @endforeach
    </div>
</div>

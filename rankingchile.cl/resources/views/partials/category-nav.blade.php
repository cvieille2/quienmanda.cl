@php
    $categories = $categories ?? collect();
    $activeCategorySlug = $activeCategorySlug ?? null;
@endphp
@if($categories->count())
<nav class="bg-white border-b border-gray-100" aria-label="Categorías">
    <div class="mx-auto max-w-6xl px-4">
        <div class="flex items-center gap-2 py-2.5 overflow-x-auto scrollbar-hide" style="-webkit-overflow-scrolling:touch">
            <a href="{{ route('home') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border-2 transition whitespace-nowrap
                      {{ !$activeCategorySlug ? 'border-[#1B1B18] bg-[#1B1B18] text-white' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                Todo
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('category.show', $cat->slug) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-sm font-semibold border-2 transition whitespace-nowrap
                      {{ $activeCategorySlug === $cat->slug ? 'border-[#F53003] bg-[#F53003]/10 text-[#F53003]' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>
</nav>
@endif

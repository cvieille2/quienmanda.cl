<div class="space-y-3 text-sm text-white/80">
    @foreach ($items as $item)
        <div class="flex gap-3 rounded-2xl bg-white/5 px-3 py-2">
            <span class="mt-0.5 text-[#F8B803]">⚖️</span>
            <p class="leading-snug">{{ $item }}</p>
        </div>
    @endforeach
</div>

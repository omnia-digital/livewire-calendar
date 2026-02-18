<div
    @if($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    x-data="{ touchStartX: 0, touchStartY: 0 }"
    @if($swipeNavigationEnabled)
        x-on:touchstart="touchStartX = $event.touches[0].clientX; touchStartY = $event.touches[0].clientY"
        x-on:touchend="
            const dx = $event.changedTouches[0].clientX - touchStartX;
            const dy = $event.changedTouches[0].clientY - touchStartY;
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
                if (dx > 0) {
                    @if($viewMode === 'month') $wire.goToPreviousMonth(); @elseif($viewMode === 'week') $wire.goToPreviousWeek(); @else $wire.goToPreviousDay(); @endif
                } else {
                    @if($viewMode === 'month') $wire.goToNextMonth(); @elseif($viewMode === 'week') $wire.goToNextWeek(); @else $wire.goToNextDay(); @endif
                }
            }
        "
    @endif
>
    @if($mobileHeaderEnabled)
        <div class="sticky top-0 z-10 border-b border-gray-100 bg-white/95 px-3 py-2 backdrop-blur-sm md:hidden">
            <div class="flex items-center justify-between">
                <button
                    @if($viewMode === 'month') wire:click="goToPreviousMonth" @elseif($viewMode === 'week') wire:click="goToPreviousWeek" @else wire:click="goToPreviousDay" @endif
                    class="-ml-2 min-h-[44px] min-w-[44px] p-2 text-gray-700"
                    aria-label="Previous"
                >
                    ‹
                </button>

                <button
                    wire:click="goToToday"
                    class="text-base font-semibold text-gray-900"
                >
                    {{ $headerLabel }}
                </button>

                <button
                    @if($viewMode === 'month') wire:click="goToNextMonth" @elseif($viewMode === 'week') wire:click="goToNextWeek" @else wire:click="goToNextDay" @endif
                    class="-mr-2 min-h-[44px] min-w-[44px] p-2 text-gray-700"
                    aria-label="Next"
                >
                    ›
                </button>
            </div>

            <div class="mt-2 grid grid-cols-3 rounded-lg bg-gray-100 p-1 text-xs font-medium">
                @foreach(['month' => 'Month', 'week' => 'Week', 'day' => 'Day'] as $mode => $label)
                    <button wire:click="setViewMode('{{ $mode }}')" class="rounded-md px-2 py-1.5 {{ $viewMode === $mode ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="hidden md:block">
        @includeIf($beforeCalendarView)
    </div>

    @if($viewMode === 'month')
        @include('livewire-calendar::month')
    @elseif($viewMode === 'week')
        @include('livewire-calendar::week')
    @else
        @include('livewire-calendar::day-detail')
    @endif

    <div class="hidden md:block">
        @includeIf($afterCalendarView)
    </div>
</div>

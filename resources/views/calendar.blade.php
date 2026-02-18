<div
    @if($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    x-data="{ touchStartX: 0, touchStartY: 0 }"
    x-on:touchstart="touchStartX = $event.touches[0].clientX; touchStartY = $event.touches[0].clientY"
    x-on:touchend="
        const dx = $event.changedTouches[0].clientX - touchStartX;
        const dy = $event.changedTouches[0].clientY - touchStartY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
            if (dx > 0) $wire.goToPreviousMonth();
            else $wire.goToNextMonth();
        }
    "
>
    <div class="md:hidden sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-gray-100">
        <div class="h-11 px-2 flex items-center justify-between gap-2">
            <button wire:click="goToPreviousMonth" class="min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Previous month">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button wire:click="goToCurrentMonth" class="text-base font-semibold text-gray-900">
                {{ $startsAt->format('F Y') }}
            </button>

            <div class="flex items-center gap-2">
                @if(!$startsAt->isSameMonth(now()))
                    <button wire:click="goToCurrentMonth" class="text-xs font-medium text-blue-600 border border-blue-200 rounded-full px-3 py-1">
                        Today
                    </button>
                @endif

                <button wire:click="goToNextMonth" class="min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Next month">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="hidden md:block">
        @includeIf($beforeCalendarView)
    </div>

    <div class="w-full">
        <div class="w-full overflow-hidden">
            <div class="w-full flex flex-row">
                @foreach($monthGrid->first() as $day)
                    @include($dayOfWeekView, ['day' => $day])
                @endforeach
            </div>

            @foreach($monthGrid as $week)
                <div class="w-full flex flex-row">
                    @foreach($week as $day)
                        @include($dayView, [
                            'componentId' => $componentId,
                            'day' => $day,
                            'dayInMonth' => $day->isSameMonth($startsAt),
                            'isToday' => $day->isToday(),
                            'events' => $getEventsForDay($day, $events),
                        ])
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <div class="md:hidden border-t border-gray-100 mt-1" id="mobile-day-detail-panel" tabindex="-1">
        @include('livewire-calendar::mobile-day-detail', [
            'day' => $selectedDay,
            'events' => $selectedDay ? $getEventsForDay($selectedDay, $events) : collect(),
        ])
    </div>

    <div class="hidden md:block">
        @includeIf($afterCalendarView)
    </div>
</div>

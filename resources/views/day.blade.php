<div
    x-data="{ dragOver: false, dragAndDropTouchEnabled: window.innerWidth >= 768 }"
    x-on:dragenter.prevent="if (dragAndDropTouchEnabled) dragOver = true"
    x-on:dragleave.prevent="if (dragAndDropTouchEnabled) dragOver = false"
    x-on:dragover.prevent
    x-on:drop.prevent="
        if (!dragAndDropTouchEnabled) return;
        dragOver = false;
        $wire.onEventDropped(
            $event.dataTransfer.getData('id'),
            {{ $day->year }},
            {{ $day->month }},
            {{ $day->day }}
        )
    "
    :class="{ '{{ $dragAndDropClasses }}': dragOver }"
    class="flex-1 h-10 md:h-40 lg:h-48 border border-gray-200 -mt-px -ml-px"
>
    <div class="w-full h-full" id="{{ $componentId }}-{{ $day }}">
        <div
            @if($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
                role="gridcell"
                aria-selected="{{ $selectedDay && $selectedDay->isSameDay($day) ? 'true' : 'false' }}"
            @endif
            class="w-full h-full p-0.5 md:p-2 flex flex-col {{ $dayInMonth ? '' : 'opacity-30 md:opacity-100 md:bg-gray-100' }} {{ $dayInMonth ? ($isToday ? 'md:bg-yellow-100' : 'bg-white') : '' }} {{ $selectedDay && $selectedDay->isSameDay($day) ? 'bg-blue-50 md:bg-inherit' : '' }}"
        >
            <div class="flex items-center justify-center md:justify-start">
                <span class="md:hidden text-xs {{ $dayInMonth ? 'font-medium' : '' }} {{ $isToday ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-semibold' : '' }}">
                    {{ $day->format('j') }}
                </span>

                <p class="hidden md:block text-sm {{ $dayInMonth ? 'font-medium' : '' }}">
                    {{ $day->format('j') }}
                </p>

                <p class="hidden md:block text-xs text-gray-600 ml-4">
                    @if($events->isNotEmpty())
                        {{ $events->count() }} {{ Str::plural('event', $events->count()) }}
                    @endif
                </p>
            </div>

            @if($events->isNotEmpty())
                <div class="flex items-center gap-0.5 justify-center mt-0.5 md:hidden">
                    @foreach($events->take(3) as $event)
                        <div class="w-1 h-1 rounded-full bg-blue-500"></div>
                    @endforeach
                    @if($events->count() > 3)
                        <span class="text-[8px] text-gray-500 leading-none">+{{ $events->count() - 3 }}</span>
                    @endif
                    <span class="sr-only">{{ $events->count() }} {{ Str::plural('event', $events->count()) }}</span>
                </div>
            @endif

            <div class="hidden md:block p-2 my-2 flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 grid-flow-row gap-2">
                    @foreach($events as $event)
                        <div
                            @if($dragAndDropEnabled && !($event['is_multiday'] ?? false))
                                x-bind:draggable="dragAndDropTouchEnabled"
                                x-on:dragstart="if (dragAndDropTouchEnabled) $event.dataTransfer.setData('id', '{{ $event['id'] }}')"
                            @endif
                        >
                            @include($eventView, ['event' => $event])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

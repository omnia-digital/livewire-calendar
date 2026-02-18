
<div
    x-data="{ dragOver: false }"
    x-on:dragenter.prevent="dragOver = true"
    x-on:dragleave.prevent="dragOver = false"
    x-on:dragover.prevent
    x-on:drop.prevent="
        dragOver = false;
        $wire.onEventDropped(
            $event.dataTransfer.getData('id'),
            {{ $day->year }},
            {{ $day->month }},
            {{ $day->day }}
        )
    "
    :class="{ '{{ $dragAndDropClasses }}': dragOver }"
    class="flex-1 h-24 lg:h-48 border border-gray-200 -mt-px -ml-px">

    {{-- Wrapper for Drag and Drop --}}
    <div
        class="w-full h-full"
        id="{{ $componentId }}-{{ $day }}">

        <div
            @if($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
            @endif
            class="w-full h-full p-1 lg:p-2 {{ $dayInMonth ? $isToday ? 'bg-yellow-100' : ' bg-white ' : 'bg-gray-100' }} flex flex-col">

            {{-- Number of Day --}}
            <div class="flex items-center">
                <p class="text-sm {{ $dayInMonth ? ' font-medium ' : '' }}">
                    {{ $day->format('j') }}
                </p>
                <p class="text-xs text-gray-600 ml-4 hidden lg:block">
                    @if($events->isNotEmpty())
                        {{ $events->count() }} {{ Str::plural('event', $events->count()) }}
                    @endif
                </p>
                <p class="text-xs text-gray-600 ml-1 lg:hidden">
                    @if($events->isNotEmpty())
                        {{ $events->count() }}
                    @endif
                </p>
            </div>

            {{-- Events --}}
            <div class="p-0.5 lg:p-2 my-1 lg:my-2 flex-1 overflow-y-auto">
                <div class="grid grid-cols-1 grid-flow-row gap-2">
                    @foreach($events as $event)
                        <div
                            @if($dragAndDropEnabled && !($event['is_multiday'] ?? false))
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('id', '{{ $event['id'] }}')"
                            @endif
                        >
                            @include($eventView, [
                                'event' => $event,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

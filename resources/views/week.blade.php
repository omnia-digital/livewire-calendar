<div class="md:hidden px-3 pb-3">
    <div class="flex gap-2 overflow-x-auto snap-x snap-mandatory">
        @foreach($weekDays as $day)
            @php($dayEvents = $getEventsForDay($day, $events))
            <button
                wire:click="selectDate({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
                class="snap-start min-w-[76px] rounded-lg border px-2 py-2 text-left {{ $selectedDate->isSameDay($day) ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-200' }}"
            >
                <p class="text-[10px] uppercase tracking-wide text-gray-500">{{ $day->format('D') }}</p>
                <p class="text-sm font-semibold {{ $day->isToday() ? 'text-blue-600' : 'text-gray-900' }}">{{ $day->format('j') }}</p>
                <p class="text-[10px] text-gray-500 mt-1">{{ $dayEvents->count() }} {{ \Illuminate\Support\Str::plural('event', $dayEvents->count()) }}</p>
            </button>
        @endforeach
    </div>

    <div class="mt-3 rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-3 py-2 text-sm font-semibold text-gray-800">
            {{ $selectedDate->format('l, M j') }}
        </div>
        <div class="max-h-[420px] overflow-y-auto p-3 space-y-2">
            @forelse($selectedWeekEvents as $event)
                <div
                    @if($eventClickEnabled)
                        wire:click.stop="onEventClick('{{ $event['id'] }}')"
                    @endif
                    class="rounded-lg border border-gray-100 p-3"
                >
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $event['title'] }}</p>
                    @if(isset($event['start_time']) || isset($event['end_time']))
                        <p class="mt-1 text-xs text-gray-500">{{ $event['start_time'] ?? 'All day' }}{{ isset($event['end_time']) ? ' - '.$event['end_time'] : '' }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No events for this day.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="hidden md:block">
    <div class="grid grid-cols-8 border border-gray-200 bg-white text-sm">
        <div class="border-r border-gray-200 p-2 text-xs font-semibold text-gray-500">Time</div>
        @foreach($weekDays as $day)
            <div class="border-r border-gray-200 p-2 text-center {{ $day->isToday() ? 'bg-blue-50' : '' }}">
                <p class="text-xs text-gray-500">{{ $day->format('D') }}</p>
                <p class="font-semibold">{{ $day->format('j') }}</p>
            </div>
        @endforeach
    </div>

    <div class="max-h-[720px] overflow-y-auto border-x border-b border-gray-200">
        @foreach($hours as $hour)
            <div class="grid grid-cols-8 border-t border-gray-100">
                <div class="border-r border-gray-200 p-2 text-xs text-gray-500">{{ $hour }}</div>
                @foreach($weekDays as $day)
                    @php($slotEvents = $getEventsForHour($day, $hourIndexMap[$hour], $events))
                    <div class="min-h-[64px] border-r border-gray-100 p-1">
                        @foreach($slotEvents as $event)
                            <div class="mb-1 rounded border border-blue-100 bg-blue-50 px-2 py-1 text-xs text-blue-900 truncate">{{ $event['title'] }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

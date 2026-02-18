<div class="md:hidden px-3 pb-3">
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-100 px-3 py-2 text-sm font-semibold text-gray-800">
            {{ $selectedDate->format('l, F j') }}
        </div>

        @if($allDayEvents->isNotEmpty())
            <div class="border-b border-gray-100 px-3 py-2">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">All day</p>
                <div class="mt-2 space-y-2">
                    @foreach($allDayEvents as $event)
                        <div @if($eventClickEnabled) wire:click.stop="onEventClick('{{ $event['id'] }}')" @endif class="rounded-lg border border-gray-100 p-2">
                            <p class="text-sm font-medium text-gray-900">{{ $event['title'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="max-h-[520px] overflow-y-auto p-3 space-y-2">
            @forelse($timedDayEvents as $event)
                <div @if($eventClickEnabled) wire:click.stop="onEventClick('{{ $event['id'] }}')" @endif class="rounded-lg border border-gray-100 p-3">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $event['title'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $event['start_time'] ?? 'All day' }}{{ isset($event['end_time']) ? ' - '.$event['end_time'] : '' }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No events for this day.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="hidden md:block border border-gray-200 bg-white">
    <div class="border-b border-gray-100 p-3 text-sm font-semibold text-gray-800">{{ $selectedDate->format('l, F j, Y') }}</div>
    <div class="max-h-[720px] overflow-y-auto">
        @foreach($hours as $hour)
            @php($slotEvents = $getEventsForHour($selectedDate, $hourIndexMap[$hour], $timedDayEvents))
            <div class="grid grid-cols-[80px_1fr] border-t border-gray-100">
                <div class="p-2 text-xs text-gray-500">{{ $hour }}</div>
                <div class="min-h-[64px] p-2">
                    @foreach($slotEvents as $event)
                        <div class="mb-2 rounded border border-blue-100 bg-blue-50 px-2 py-2 text-sm text-blue-900">
                            <p class="font-medium">{{ $event['title'] }}</p>
                            <p class="text-xs text-blue-700">{{ $event['start_time'] ?? '' }}{{ isset($event['end_time']) ? ' - '.$event['end_time'] : '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

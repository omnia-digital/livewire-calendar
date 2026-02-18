@if($day)
    <div class="p-3 space-y-3">
        <p class="text-sm font-semibold text-gray-900">
            {{ $day->format('l, M j') }}
        </p>

        @if($events->isEmpty())
            <p class="text-sm text-gray-400">No events</p>
        @else
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @foreach($events as $event)
                    <div
                        @if($eventClickEnabled)
                            wire:click.stop="onEventClick('{{ $event['id']  }}')"
                        @endif
                        class="flex items-start gap-3 p-3 rounded-lg bg-white border border-gray-100 active:bg-gray-50 transition-colors cursor-pointer"
                    >
                        <div class="w-1 self-stretch rounded-full bg-blue-500 flex-shrink-0"></div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $event['title'] }}</p>

                            @if(($event['is_multiday'] ?? false))
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    Day {{ $event['day_position'] ?? 1 }} of {{ $event['total_days'] ?? 1 }}
                                </p>
                            @endif

                            @if(isset($event['start_time']) || isset($event['end_time']))
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $event['start_time'] ?? 'All day' }}@if(isset($event['end_time'])) – {{ $event['end_time'] }}@endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@else
    <div class="p-4 text-center text-sm text-gray-400">Tap a day to see events</div>
@endif

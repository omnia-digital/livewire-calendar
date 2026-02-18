<div
    @if($eventClickEnabled)
        wire:click.stop="onEventClick('{{ $event['id']  }}')"
    @endif
    class="py-1 px-1.5 lg:py-2 lg:px-3 shadow-md cursor-pointer
        @if($event['is_multiday'] ?? false)
            @if($event['is_first_day'] ?? false)
                rounded-l-lg rounded-r-none border-r-0
            @elseif($event['is_last_day'] ?? false)
                rounded-r-lg rounded-l-none border-l-0
            @else
                rounded-none border-l-0 border-r-0
            @endif
            bg-blue-50 border border-blue-200
        @else
            bg-white rounded-lg border
        @endif
    ">

    <p class="text-xs lg:text-sm font-medium truncate">
        {{ $event['title'] }}
        @if(($event['is_multiday'] ?? false) && ($event['is_first_day'] ?? false))
            <span class="text-xs text-gray-500 font-normal">
                ({{ $event['total_days'] ?? 1 }} days)
            </span>
        @endif
    </p>

    @if($event['is_first_day'] ?? true)
        <p class="mt-1 lg:mt-2 text-xs hidden lg:block">
            {{ $event['description'] ?? 'No description' }}
        </p>
    @endif

    {{-- Time display for events with time info --}}
    @if(isset($event['start_time']) || isset($event['end_time']))
        <p class="mt-1 text-xs text-gray-500">
            @if(($event['is_first_day'] ?? true) && isset($event['start_time']))
                {{ $event['start_time'] }}
            @endif
            @if(($event['is_last_day'] ?? true) && isset($event['end_time']))
                @if(($event['is_first_day'] ?? true) && isset($event['start_time']))
                    -
                @endif
                {{ $event['end_time'] }}
            @endif
        </p>
    @endif
</div>

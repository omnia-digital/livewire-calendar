<div class="flex">
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

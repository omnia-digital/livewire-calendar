<?php

namespace Omnia\LivewireCalendar;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Class LivewireCalendar
 * @package Omnia\LivewireCalendar
 */
class LivewireCalendar extends Component
{
    public $startsAt;
    public $endsAt;

    public $gridStartsAt;
    public $gridEndsAt;

    public $weekStartsAt;
    public $weekEndsAt;

    public $calendarView;
    public $dayView;
    public $eventView;
    public $dayOfWeekView;

    public $dragAndDropClasses;

    public $beforeCalendarView;
    public $afterCalendarView;

    public $pollMillis;
    public $pollAction;

    public $dragAndDropEnabled;
    public $dayClickEnabled;
    public $eventClickEnabled;

    public $viewMode;
    public $selectedDate;
    public $mobileHeaderEnabled;
    public $swipeNavigationEnabled;

    protected $casts = [
        'startsAt' => 'date',
        'endsAt' => 'date',
        'gridStartsAt' => 'date',
        'gridEndsAt' => 'date',
        'selectedDate' => 'date',
    ];

    public function mount($initialYear = null,
                          $initialMonth = null,
                          $weekStartsAt = null,
                          $calendarView = null,
                          $dayView = null,
                          $eventView = null,
                          $dayOfWeekView = null,
                          $dragAndDropClasses = null,
                          $beforeCalendarView = null,
                          $afterCalendarView = null,
                          $pollMillis = null,
                          $pollAction = null,
                          $dragAndDropEnabled = true,
                          $dayClickEnabled = true,
                          $eventClickEnabled = true,
                          $viewMode = 'month',
                          $mobileHeaderEnabled = true,
                          $swipeNavigationEnabled = true,
                          $selectedDate = null,
                          $extras = [])
    {
        $this->weekStartsAt = $weekStartsAt ?? Carbon::SUNDAY;
        $this->weekEndsAt = $this->weekStartsAt == Carbon::SUNDAY
            ? Carbon::SATURDAY
            : collect([0, 1, 2, 3, 4, 5, 6])->get($this->weekStartsAt + 6 - 7);

        $initialYear = $initialYear ?? Carbon::today()->year;
        $initialMonth = $initialMonth ?? Carbon::today()->month;

        $this->startsAt = Carbon::createFromDate($initialYear, $initialMonth, 1)->startOfDay();
        $this->endsAt = $this->startsAt->clone()->endOfMonth()->startOfDay();

        $this->selectedDate = $selectedDate ? Carbon::parse($selectedDate)->startOfDay() : Carbon::today()->startOfDay();
        $this->viewMode = in_array($viewMode, ['month', 'week', 'day'], true) ? $viewMode : 'month';
        $this->mobileHeaderEnabled = $mobileHeaderEnabled;
        $this->swipeNavigationEnabled = $swipeNavigationEnabled;

        $this->calculateGridStartsEnds();

        $this->setupViews($calendarView, $dayView, $eventView, $dayOfWeekView, $beforeCalendarView, $afterCalendarView);

        $this->setupPoll($pollMillis, $pollAction);

        $this->dragAndDropEnabled = $dragAndDropEnabled;
        $this->dragAndDropClasses = $dragAndDropClasses ?? 'border border-blue-400 border-4';

        $this->dayClickEnabled = $dayClickEnabled;
        $this->eventClickEnabled = $eventClickEnabled;

        $this->afterMount($extras);
    }

    public function afterMount($extras = [])
    {
        //
    }

    public function setupViews($calendarView = null,
                               $dayView = null,
                               $eventView = null,
                               $dayOfWeekView = null,
                               $beforeCalendarView = null,
                               $afterCalendarView = null)
    {
        $this->calendarView = $calendarView ?? 'livewire-calendar::calendar';
        $this->dayView = $dayView ?? 'livewire-calendar::day';
        $this->eventView = $eventView ?? 'livewire-calendar::event';
        $this->dayOfWeekView = $dayOfWeekView ?? 'livewire-calendar::day-of-week';

        $this->beforeCalendarView = $beforeCalendarView ?? null;
        $this->afterCalendarView = $afterCalendarView ?? null;
    }

    public function setupPoll($pollMillis, $pollAction)
    {
        $this->pollMillis = $pollMillis;
        $this->pollAction = $pollAction;
    }

    public function setViewMode(string $mode)
    {
        if (in_array($mode, ['month', 'week', 'day'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function selectDate($year, $month, $day)
    {
        $this->selectedDate = Carbon::createFromDate($year, $month, $day)->startOfDay();
        $this->startsAt = $this->selectedDate->copy()->startOfMonth();
        $this->endsAt = $this->selectedDate->copy()->endOfMonth()->startOfDay();
        $this->calculateGridStartsEnds();
    }

    public function goToToday()
    {
        $this->selectedDate = Carbon::today()->startOfDay();
        $this->goToCurrentMonth();
    }

    public function goToPreviousMonth()
    {
        $this->startsAt->subMonthNoOverflow();
        $this->endsAt->subMonthNoOverflow();

        $this->calculateGridStartsEnds();
    }

    public function goToNextMonth()
    {
        $this->startsAt->addMonthNoOverflow();
        $this->endsAt->addMonthNoOverflow();

        $this->calculateGridStartsEnds();
    }

    public function goToPreviousWeek()
    {
        $this->selectedDate = $this->selectedDate->copy()->subWeek()->startOfDay();
        $this->startsAt = $this->selectedDate->copy()->startOfMonth();
        $this->endsAt = $this->selectedDate->copy()->endOfMonth()->startOfDay();
        $this->calculateGridStartsEnds();
    }

    public function goToNextWeek()
    {
        $this->selectedDate = $this->selectedDate->copy()->addWeek()->startOfDay();
        $this->startsAt = $this->selectedDate->copy()->startOfMonth();
        $this->endsAt = $this->selectedDate->copy()->endOfMonth()->startOfDay();
        $this->calculateGridStartsEnds();
    }

    public function goToPreviousDay()
    {
        $this->selectedDate = $this->selectedDate->copy()->subDay()->startOfDay();
        $this->startsAt = $this->selectedDate->copy()->startOfMonth();
        $this->endsAt = $this->selectedDate->copy()->endOfMonth()->startOfDay();
        $this->calculateGridStartsEnds();
    }

    public function goToNextDay()
    {
        $this->selectedDate = $this->selectedDate->copy()->addDay()->startOfDay();
        $this->startsAt = $this->selectedDate->copy()->startOfMonth();
        $this->endsAt = $this->selectedDate->copy()->endOfMonth()->startOfDay();
        $this->calculateGridStartsEnds();
    }

    public function goToCurrentMonth()
    {
        $this->startsAt = Carbon::today()->startOfMonth()->startOfDay();
        $this->endsAt = $this->startsAt->clone()->endOfMonth()->startOfDay();

        $this->calculateGridStartsEnds();
    }

    public function calculateGridStartsEnds()
    {
        $this->gridStartsAt = $this->startsAt->clone()->startOfWeek($this->weekStartsAt)->shiftTimezone(config('app.timezone'));
        $this->gridEndsAt = $this->endsAt->clone()->endOfWeek($this->weekEndsAt)->shiftTimezone(config('app.timezone'));
    }

    /**
     * @throws Exception
     */
    public function monthGrid()
    {
        $firstDayOfGrid = $this->gridStartsAt;
        $lastDayOfGrid = $this->gridEndsAt;

        $numbersOfWeeks = floor(abs($firstDayOfGrid->diffInWeeks($lastDayOfGrid)) + 1);
        $days = floor(abs($firstDayOfGrid->diffInDays($lastDayOfGrid)) + 1);

        if ($days % 7 != 0) {
            throw new Exception('Livewire Calendar not correctly configured. Check initial inputs.');
        }

        $monthGrid = collect();
        $currentDay = $firstDayOfGrid->clone();

        while (!$currentDay->greaterThan($lastDayOfGrid)) {
            $monthGrid->push($currentDay->clone());
            $currentDay->addDay();
        }

        $monthGrid = $monthGrid->chunk(7);
        if ($numbersOfWeeks != $monthGrid->count()) {
            throw new Exception('Livewire Calendar calculated wrong number of weeks. Sorry :(');
        }

        return $monthGrid;
    }

    public function weekDays(): Collection
    {
        $start = $this->selectedDate->copy()->startOfWeek($this->weekStartsAt);

        return collect(range(0, 6))->map(function ($offset) use ($start) {
            return $start->copy()->addDays($offset);
        });
    }

    public function timeSlots(): Collection
    {
        return collect(range(0, 23))->map(function ($hour) {
            return Carbon::createFromTime($hour)->format('g:00 A');
        });
    }

    public function events(): Collection
    {
        return collect();
    }

    public function getEventsForDay($day, Collection $events): Collection
    {
        return $events
            ->filter(function ($event) use ($day) {
                return $this->eventOccursOnDay($event, $day);
            })
            ->map(function ($event) use ($day) {
                return $this->enrichEventForDay($event, $day);
            });
    }

    public function getEventsForHour(Carbon $day, int $hour, Collection $events): Collection
    {
        return $this->getEventsForDay($day, $events)->filter(function ($event) use ($hour) {
            if (!isset($event['start_time'])) {
                return false;
            }

            try {
                return Carbon::parse($event['start_time'])->hour === $hour;
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    protected function eventOccursOnDay(array $event, Carbon $day): bool
    {
        if (isset($event['start_date']) && isset($event['end_date'])) {
            $startDate = Carbon::parse($event['start_date'])->startOfDay();
            $endDate = Carbon::parse($event['end_date'])->startOfDay();
            $checkDay = $day->copy()->startOfDay();

            return $checkDay->between($startDate, $endDate);
        }

        if (isset($event['date'])) {
            return Carbon::parse($event['date'])->isSameDay($day);
        }

        return false;
    }

    protected function enrichEventForDay(array $event, Carbon $day): array
    {
        if (!isset($event['start_date']) || !isset($event['end_date'])) {
            $event['is_multiday'] = false;
            $event['is_first_day'] = true;
            $event['is_last_day'] = true;
            $event['day_position'] = 1;
            $event['total_days'] = 1;

            return $event;
        }

        $startDate = Carbon::parse($event['start_date'])->startOfDay();
        $endDate = Carbon::parse($event['end_date'])->startOfDay();
        $checkDay = $day->copy()->startOfDay();

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $dayPosition = $startDate->diffInDays($checkDay) + 1;

        $event['is_multiday'] = $totalDays > 1;
        $event['is_first_day'] = $checkDay->isSameDay($startDate);
        $event['is_last_day'] = $checkDay->isSameDay($endDate);
        $event['day_position'] = $dayPosition;
        $event['total_days'] = $totalDays;

        return $event;
    }

    public function onDayClick($year, $month, $day)
    {
        $this->selectDate($year, $month, $day);
    }

    public function onEventClick($eventId)
    {
        //
    }

    public function onEventDropped($eventId, $year, $month, $day)
    {
        //
    }

    public function getId()
    {
        if (method_exists(parent::class, 'getId')) {
            return parent::getId();
        }

        if (!empty($this->__id)) {
            return $this->__id;
        }

        if (!empty($this->id)) {
            return $this->id;
        }

        return 'livewire-calendar-' . uniqid();
    }

    public function getHeaderLabelProperty(): string
    {
        if ($this->viewMode === 'week') {
            $weekStart = $this->selectedDate->copy()->startOfWeek($this->weekStartsAt);
            $weekEnd = $weekStart->copy()->endOfWeek($this->weekEndsAt);

            return $weekStart->format('M j') . ' - ' . $weekEnd->format('M j');
        }

        if ($this->viewMode === 'day') {
            return $this->selectedDate->format('D, M j');
        }

        return $this->startsAt->format('F Y');
    }

    /**
     * @return Factory|View
     * @throws Exception
     */
    public function render()
    {
        $events = $this->events();
        $hours = $this->timeSlots();
        $hourIndexMap = $hours->mapWithKeys(function ($value, $index) {
            return [$value => $index];
        });

        $selectedDayEvents = $this->getEventsForDay($this->selectedDate, $events);
        $allDayEvents = $selectedDayEvents->filter(function ($event) {
            return !isset($event['start_time']) && !isset($event['end_time']);
        });
        $timedDayEvents = $selectedDayEvents->reject(function ($event) {
            return !isset($event['start_time']) && !isset($event['end_time']);
        })->sortBy('start_time')->values();

        return view($this->calendarView)
            ->with([
                'componentId' => $this->getId(),
                'monthGrid' => $this->monthGrid(),
                'events' => $events,
                'weekDays' => $this->weekDays(),
                'hours' => $hours,
                'hourIndexMap' => $hourIndexMap,
                'selectedWeekEvents' => $selectedDayEvents,
                'allDayEvents' => $allDayEvents,
                'timedDayEvents' => $timedDayEvents,
                'headerLabel' => $this->headerLabel,
                'getEventsForDay' => function ($day) use ($events) {
                    return $this->getEventsForDay($day, $events);
                },
                'getEventsForHour' => function ($day, $hour, $eventsInput = null) use ($events) {
                    return $this->getEventsForHour($day, $hour, $eventsInput ?? $events);
                },
            ]);
    }
}

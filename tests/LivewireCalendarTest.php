<?php

namespace Omnia\LivewireCalendar\Tests;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Omnia\LivewireCalendar\LivewireCalendar;
use Livewire\Livewire;

class LivewireCalendarTest extends TestCase
{
    private function createComponent($parameters = [])
    {
        return Livewire::test(LivewireCalendar::class, $parameters);
    }

    public function test_can_build_component()
    {
        $component = $this->createComponent([]);

        $this->assertNotNull($component);
    }

    public function test_can_navigate_to_next_month()
    {
        $component = $this->createComponent([]);

        $component->call('goToNextMonth');

        $this->assertEquals(
            today()->startOfMonth()->addMonthNoOverflow(),
            $component->get('startsAt')
        );

        $this->assertEquals(
            today()->endOfMonth()->startOfDay()->addMonthNoOverflow(),
            $component->get('endsAt')
        );
    }

    public function test_can_navigate_to_previous_month()
    {
        $component = $this->createComponent([]);

        $component->call('goToPreviousMonth');

        $this->assertEquals(
            today()->startOfMonth()->subMonthNoOverflow(),
            $component->get('startsAt')
        );

        $this->assertEquals(
            today()->endOfMonth()->startOfDay()->subMonthNoOverflow(),
            $component->get('endsAt')
        );
    }

    public function test_can_navigate_to_current_month()
    {
        $component = $this->createComponent([]);

        $component->call('goToPreviousMonth');
        $component->call('goToPreviousMonth');
        $component->call('goToPreviousMonth');

        $component->call('goToCurrentMonth');

        $this->assertEquals(
            today()->startOfMonth(),
            $component->get('startsAt')
        );

        $this->assertEquals(
            today()->endOfMonth()->startOfDay(),
            $component->get('endsAt')
        );
    }

    public function test_legacy_single_day_event_shows_on_correct_day()
    {
        $calendar = new LivewireCalendar();
        $today = Carbon::today();

        $events = collect([
            [
                'id' => 1,
                'title' => 'Single Day Event',
                'date' => $today,
            ]
        ]);

        $result = $calendar->getEventsForDay($today, $events);

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()['id']);
        $this->assertFalse($result->first()['is_multiday']);
    }

    public function test_legacy_event_does_not_show_on_wrong_day()
    {
        $calendar = new LivewireCalendar();
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $events = collect([
            [
                'id' => 1,
                'title' => 'Single Day Event',
                'date' => $today,
            ]
        ]);

        $result = $calendar->getEventsForDay($tomorrow, $events);

        $this->assertCount(0, $result);
    }

    public function test_multiday_event_shows_on_all_days_in_range()
    {
        $calendar = new LivewireCalendar();
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(2);

        $events = collect([
            [
                'id' => 1,
                'title' => 'Multi-Day Event',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);

        // Should appear on day 1 (start)
        $result = $calendar->getEventsForDay($startDate, $events);
        $this->assertCount(1, $result);
        $this->assertTrue($result->first()['is_first_day']);
        $this->assertFalse($result->first()['is_last_day']);
        $this->assertTrue($result->first()['is_multiday']);

        // Should appear on day 2 (middle)
        $result = $calendar->getEventsForDay($startDate->copy()->addDay(), $events);
        $this->assertCount(1, $result);
        $this->assertFalse($result->first()['is_first_day']);
        $this->assertFalse($result->first()['is_last_day']);

        // Should appear on day 3 (end)
        $result = $calendar->getEventsForDay($endDate, $events);
        $this->assertCount(1, $result);
        $this->assertFalse($result->first()['is_first_day']);
        $this->assertTrue($result->first()['is_last_day']);
    }

    public function test_multiday_event_does_not_show_outside_range()
    {
        $calendar = new LivewireCalendar();
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(2);

        $events = collect([
            [
                'id' => 1,
                'title' => 'Multi-Day Event',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);

        // Should NOT appear before start
        $result = $calendar->getEventsForDay($startDate->copy()->subDay(), $events);
        $this->assertCount(0, $result);

        // Should NOT appear after end
        $result = $calendar->getEventsForDay($endDate->copy()->addDay(), $events);
        $this->assertCount(0, $result);
    }

    public function test_event_with_same_start_and_end_date_is_single_day()
    {
        $calendar = new LivewireCalendar();
        $today = Carbon::today();

        $events = collect([
            [
                'id' => 1,
                'title' => 'Same Day Event',
                'start_date' => $today,
                'end_date' => $today,
            ]
        ]);

        $result = $calendar->getEventsForDay($today, $events);

        $this->assertCount(1, $result);
        $this->assertFalse($result->first()['is_multiday']);
        $this->assertEquals(1, $result->first()['total_days']);
    }

    public function test_event_day_position_is_calculated_correctly()
    {
        $calendar = new LivewireCalendar();
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(4); // 5 day event

        $events = collect([
            [
                'id' => 1,
                'title' => 'Five Day Event',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);

        // Check day 3 (middle)
        $day3 = $startDate->copy()->addDays(2);
        $result = $calendar->getEventsForDay($day3, $events);

        $this->assertEquals(3, $result->first()['day_position']);
        $this->assertEquals(5, $result->first()['total_days']);
    }

    public function test_start_date_takes_precedence_over_legacy_date()
    {
        $calendar = new LivewireCalendar();
        $legacyDate = Carbon::today();
        $startDate = Carbon::today()->addDays(5);
        $endDate = Carbon::today()->addDays(7);

        $events = collect([
            [
                'id' => 1,
                'title' => 'Mixed Format Event',
                'date' => $legacyDate,  // Legacy field - should be ignored
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);

        // Should NOT appear on legacy date
        $result = $calendar->getEventsForDay($legacyDate, $events);
        $this->assertCount(0, $result);

        // Should appear on start_date
        $result = $calendar->getEventsForDay($startDate, $events);
        $this->assertCount(1, $result);
    }

    public function test_multiple_events_on_same_day()
    {
        $calendar = new LivewireCalendar();
        $today = Carbon::today();

        $events = collect([
            [
                'id' => 1,
                'title' => 'Legacy Event',
                'date' => $today,
            ],
            [
                'id' => 2,
                'title' => 'Multi-Day Event (day 2 of 3)',
                'start_date' => $today->copy()->subDay(),
                'end_date' => $today->copy()->addDay(),
            ],
            [
                'id' => 3,
                'title' => 'Single Day New Format',
                'start_date' => $today,
                'end_date' => $today,
            ]
        ]);

        $result = $calendar->getEventsForDay($today, $events);

        $this->assertCount(3, $result);
    }

    public function test_drag_and_drop_enabled_by_default()
    {
        $component = $this->createComponent([]);

        $this->assertTrue($component->get('dragAndDropEnabled'));
    }

    public function test_drag_and_drop_can_be_disabled()
    {
        $component = $this->createComponent(['dragAndDropEnabled' => false]);

        $this->assertFalse($component->get('dragAndDropEnabled'));
    }

    public function test_drag_and_drop_classes_have_default()
    {
        $component = $this->createComponent([]);

        $this->assertEquals('border border-blue-400 border-4', $component->get('dragAndDropClasses'));
    }

    public function test_custom_drag_and_drop_classes()
    {
        $component = $this->createComponent(['dragAndDropClasses' => 'bg-blue-100']);

        $this->assertEquals('bg-blue-100', $component->get('dragAndDropClasses'));
    }

    public function test_view_mode_defaults_to_month()
    {
        $component = $this->createComponent([]);

        $this->assertEquals('month', $component->get('viewMode'));
    }

    public function test_can_switch_view_modes()
    {
        $component = $this->createComponent([]);

        $component->call('setViewMode', 'week');
        $this->assertEquals('week', $component->get('viewMode'));

        $component->call('setViewMode', 'day');
        $this->assertEquals('day', $component->get('viewMode'));
    }

    public function test_can_navigate_week_and_day()
    {
        $component = $this->createComponent([]);

        $selectedDate = Carbon::parse($component->get('selectedDate'));

        $component->call('goToNextWeek');
        $this->assertTrue(Carbon::parse($component->get('selectedDate'))->isSameDay($selectedDate->copy()->addWeek()));

        $component->call('goToPreviousDay');
        $this->assertTrue(Carbon::parse($component->get('selectedDate'))->isSameDay($selectedDate->copy()->addDays(6)));
    }
}

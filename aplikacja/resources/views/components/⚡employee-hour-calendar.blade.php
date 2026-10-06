<?php

use Livewire\Component;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
new class extends Component {
    public ?string $selectedDay = null;
    public ?Employee $selectedEmployee = null;
    public ?int $duration = null;


    public function render()
    {
        $slots = [];

        if ($this->selectedDay && $this->selectedEmployee) {
            $date = Carbon::parse($this->selectedDay);
            $duration = $this->duration ?: 30;
            $now = now();

            // an employee can have several working ranges in one day (e.g. 9-12 and 13-17)
            $availabilities = $this->selectedEmployee->availability->where('day_of_week', $date->isoWeekday());

            // raw H:i:s strings - the model casts would attach today's date instead of the selected day
            $booked = $this->selectedEmployee->reservation()
                ->whereDate('reservation_date', $this->selectedDay)
                ->where('status', '!=', 'cancelled')
                ->toBase()
                ->get(['start_time', 'end_time'])
                ->map(fn($r) => [
                    $date->copy()->setTimeFromTimeString($r->start_time),
                    $date->copy()->setTimeFromTimeString($r->end_time),
                ]);

            foreach ($availabilities as $availability) {
                // start_time/end_time are cast to Carbon with today's date, so take only the time part
                $workStart = $date->copy()->setTimeFromTimeString(Carbon::parse($availability->start_time)->format('H:i:s'));
                $workEnd = $date->copy()->setTimeFromTimeString(Carbon::parse($availability->end_time)->format('H:i:s'));
                $lastStart = $workEnd->copy()->subMinutes($duration);

                if ($lastStart->lt($workStart)) {
                    continue; // service doesn't fit in this range
                }

                foreach (CarbonPeriod::create($workStart, '30 minutes', $lastStart) as $slotStart) {
                    $slotEnd = $slotStart->copy()->addMinutes($duration);

                    // ranges overlap when existing.start < new.end AND existing.end > new.start
                    $overlaps = $booked->contains(fn($b) => $b[0]->lt($slotEnd) && $b[1]->gt($slotStart));

                    $slots[] = [
                        'time' => $slotStart->format('H:i'),
                        'isAvailable' => !$overlaps && $slotStart->gt($now),
                    ];
                }
            }
        }

        return view('components.employee-hour-calendar', ['_slots' => $slots]);
    }
};
?>
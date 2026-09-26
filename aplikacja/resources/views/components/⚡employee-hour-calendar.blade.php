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
            $dayOfWeek = $date->isoWeekday();

            $availability = $this->selectedEmployee->availability->firstWhere('day_of_week', $dayOfWeek);

            if ($availability) {
                $startTime = Carbon::parse($this->selectedDay)->setTimeFromTimeString($availability->start_time);
                $endTime = Carbon::parse($this->selectedDay)->setTimeFromTimeString($availability->end_time);

                $period = CarbonPeriod::create($startTime, '100 minutes', $endTime->subMinutes($this->duration));

                $booked = $this->selectedEmployee->reservation()->whereDate('reservation_date', $this->selectedDay)
                    ->pluck('start_time')
                    ->map(fn($time) => Carbon::parse($time)->format('H:i'))
                    ->toArray();

                foreach ($period as $slot) {
                    $formattedSlot = $slot->format('H:i');
                    $slots[] = [
                        'time' => $formattedSlot,
                        'isAvailable' => !in_array($formattedSlot, $booked),
                    ];
                }
            }
        }

        return view('components.employee-hour-calendar', ['_slots' => $slots]);
    }
};
?>
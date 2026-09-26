<?php

use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\On;
new class extends Component {
    public array $workingDays = [];
    public int $weekOffset = 0;

    protected $listeners = ['employee-selected' => 'setAvailability'];

    #[On('employee-selected')]
    public function setAvailability($days)
    {

        foreach ($days as $day) {
            $this->workingDays[] = $day;
        }


        $this->weekOffset = 0;
    }

    public function previousWeek()
    {
        if ($this->weekOffset > 0) {
            $this->weekOffset--;
        }
    }

    public function nextWeek()
    {
        $this->weekOffset++;
    }

    public function selectDay($date)
    {
        $this->dispatch('daySelected', date: $date);
    }

    public function render()
    {
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($this->weekOffset);
        $today = Carbon::today();

        $days = collect(range(0, 6))->map(function ($i) use ($startOfWeek, $today) {
            $date = $startOfWeek->copy()->addDays($i);
            return [
                'date' => $date,
                'isWorking' => in_array($date->isoWeekday(), $this->workingDays), // 1=Mon ... 7=Sun
                'isPast' => $date->lt($today),
            ];

        });
        return view('components.employee-week-calendar', [
            'days' => $days,
            'weekOffset' => $this->weekOffset,
        ]);
    }
};
?>

<div>
    {{-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca --}}
</div>
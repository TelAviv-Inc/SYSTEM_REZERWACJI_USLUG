<?php

use App\Models\Service;
use Livewire\Component;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public ?ServiceCategory $selectedCategory = null;
    public ?Collection $services = null;

    protected $listeners = ['categorySelected'];

    public function categorySelected($categoryName)
    {
        $this->selectedCategory = ServiceCategory::where('name', $categoryName)->first();
        $categoryUUID = $this->selectedCategory->uuid;
        $this->services = Service::where('category_id', $categoryUUID)->get();
    }
    public function render()
    {
        $categories = ServiceCategory::all(['name', 'description', 'icon']);
        return view('components.show-services', ["categories" => $categories]);
    }
};
?>
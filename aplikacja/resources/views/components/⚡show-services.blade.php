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

        if (!$this->selectedCategory)
            return;

        $categoryUUID = $this->selectedCategory->uuid;
        $this->services = Service::where('category_id', $categoryUUID)->get();

        // Fired after the new list is in the DOM, the view scrolls to it
        $this->dispatch('category-services-shown');
    }
    public function render()
    {
        $categories = ServiceCategory::all(['name', 'description', 'icon']);
        return view('components.show-services', ["categories" => $categories]);
    }
};
?>
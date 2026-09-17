<?php

use App\Models\Service;
use Livewire\Component;
use App\Models\ServiceCategory;
new class extends Component {
    public $selectedCategory = '';
    public $services = [];

    protected $listeners = ['categorySelected'];

    public function categorySelected($categoryName)
    {
        $this->selectedCategory = $categoryName;
        $categoryUUID = ServiceCategory::where('name', $this->selectedCategory)->value('uuid');
        $this->services = Service::where('category_id', $categoryUUID)->get();
    }
    public function render()
    {
        $categories = ServiceCategory::all(['name', 'description', 'icon']);
        return view('components.show-services', ["categories" => $categories]);
    }
};
?>
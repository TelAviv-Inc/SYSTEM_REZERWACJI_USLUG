<?php

use Livewire\Component;
use App\Models\ServiceCategory;
new class extends Component {
    public $selectedCategory = '';

    protected $listeners = ['categorySelected'];

    public function categorySelected($categoryName)
    {
        $this->selectedCategory = $categoryName;
    }

    public function render()
    {
        $categories = ServiceCategory::all(['name', 'description', 'icon']);
        return view('components.show-services', ["categories" => $categories]);
    }
};
?>
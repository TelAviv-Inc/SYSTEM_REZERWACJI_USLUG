<?php

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->category = ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Strzyżenie i modelowanie', 'icon' => 'fa-solid fa-scissors']);
});

function categoryService(ServiceCategory $category, array $attributes = []): Service
{
    return Service::forceCreate($attributes + [
        'uuid' => (string) Str::uuid(),
        'category_id' => $category->uuid,
        'name' => 'Strzyzenie '.Str::random(6),
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);
}

test('only admins can open the categories tab', function (string $role) {
    Livewire::actingAs(User::factory()->create(['role' => $role, 'active' => true]))
        ->test('admin-categories')
        ->assertForbidden();
})->with(['client', 'employee']);

test('lists categories with their service count', function () {
    categoryService($this->category);
    categoryService($this->category, ['active' => 0]);

    $component = Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->assertSee('Fryzjer')
        ->assertSee('(aktywne:');

    expect($component->get('categories')->first())
        ->services_count->toBe(2)
        ->active_services_count->toBe(1);
});

test('search filters the categories', function () {
    ServiceCategory::create(['name' => 'Kosmetyka', 'description' => 'Paznokcie', 'icon' => 'fa-solid fa-spa']);

    $names = Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->set('search', 'Kosm')
        ->get('categories')
        ->pluck('name')
        ->all();

    expect($names)->toBe(['Kosmetyka']);
});

test('admin adds a category', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('create')
        ->assertSet('showForm', true)
        ->set('name', '  Kosmetyka ')
        ->set('description', 'Paznokcie i makijaż')
        ->set('icon', 'fa-solid   fa-spa')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false)
        ->assertSee('Kosmetyka');

    $category = ServiceCategory::where('name', 'Kosmetyka')->first();
    expect($category)->not->toBeNull()
        ->and($category->uuid)->not->toBeEmpty()
        ->and($category->icon)->toBe('fa-solid fa-spa');
});

test('admin edits a category', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('edit', $this->category->uuid)
        ->assertSet('name', 'Fryzjer')
        ->set('name', 'Fryzjerstwo')
        ->set('description', 'Nowy opis')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->category->fresh())
        ->name->toBe('Fryzjerstwo')
        ->description->toBe('Nowy opis');
});

test('saving a category keeps its own name valid', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('edit', $this->category->uuid)
        ->set('description', 'Inny opis')
        ->call('save')
        ->assertHasNoErrors();
});

test('category form is validated', function (string $field, string $value, string $rule) {
    ServiceCategory::create(['name' => 'Kosmetyka', 'description' => 'Paznokcie', 'icon' => 'fa-solid fa-spa']);

    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('create')
        ->set(['name' => 'Nowa', 'description' => 'Opis', 'icon' => 'fa-solid fa-star'])
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors([$field => $rule]);

    expect(ServiceCategory::count())->toBe(2);
})->with([
    'empty name' => ['name', '', 'required'],
    'taken name' => ['name', 'Kosmetyka', 'unique'],
    'long name' => ['name', str_repeat('a', 65), 'max'],
    'empty description' => ['description', '', 'required'],
    'icon with markup' => ['icon', 'fa-solid" onclick="x', 'regex'],
]);

test('admin deletes an empty category', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('delete', $this->category->uuid)
        ->assertDontSee('Fryzjer');

    expect(ServiceCategory::find($this->category->uuid))->toBeNull();
});

test('a category with services cannot be deleted', function () {
    $service = categoryService($this->category);

    Livewire::actingAs($this->admin)
        ->test('admin-categories')
        ->call('delete', $this->category->uuid)
        ->assertForbidden();

    expect(ServiceCategory::find($this->category->uuid))->not->toBeNull()
        ->and(Service::find($service->uuid))->not->toBeNull();
});

test('the admin menu shows the categories tab', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-menu')
        ->set('option', 'categories')
        ->assertSeeLivewire('admin-categories');
});

<?php

use App\Models\Employee;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    // Fixed, unusual values so faker data can't match the search by accident
    $this->anna = Employee::factory()->create([
        'user_id' => User::factory()->create([
            'role' => 'employee',
            'active' => true,
            'name' => 'Annabella',
            'surname' => 'Zielinska',
            'email' => 'annabella@example.test',
            'phone' => '+48111222333',
        ])->uuid,
    ]);

    $this->jan = Employee::factory()->create([
        'user_id' => User::factory()->create([
            'role' => 'employee',
            'active' => true,
            'name' => 'Janusz',
            'surname' => 'Kowalczykowski',
            'email' => 'janusz@example.test',
            'phone' => '+48999888777',
        ])->uuid,
    ]);
});

function searchEmployees($test, string $search): array
{
    return Livewire::actingAs($test->admin)
        ->test('admin-employees')
        ->set('search', $search)
        ->get('employees')
        ->pluck('uuid')
        ->all();
}

test('empty search lists every employee', function () {
    expect(searchEmployees($this, ''))->toEqualCanonicalizing([$this->anna->uuid, $this->jan->uuid]);
});

test('search matches name, surname, email and phone', function (string $search) {
    expect(searchEmployees($this, $search))->toBe([$this->anna->uuid]);
})->with([
    'name' => 'Annab',
    'surname' => 'Zielin',
    'email' => 'annabella@',
    'phone' => '111222',
]);

test('search with no match shows the empty state', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-employees')
        ->set('search', 'nikogo-takiego')
        ->assertSee('Brak pracowników.')
        ->assertDontSee('Annabella')
        ->assertDontSee('Janusz');
});

test('search combines with the status filter', function () {
    $this->jan->update(['active' => 0]);

    expect(searchEmployees($this, 'example.test'))->toEqualCanonicalizing([$this->anna->uuid, $this->jan->uuid]);

    $result = Livewire::actingAs($this->admin)
        ->test('admin-employees')
        ->set('search', 'example.test')
        ->set('status', 'active')
        ->get('employees')
        ->pluck('uuid')
        ->all();

    expect($result)->toBe([$this->anna->uuid]);
});

test('changing the search goes back to page 1', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['employees_page' => 2])
        ->test('admin-employees')
        ->assertSet('paginators.employees_page', 2)
        ->set('search', 'Annab')
        ->assertSet('paginators.employees_page', 1);
});

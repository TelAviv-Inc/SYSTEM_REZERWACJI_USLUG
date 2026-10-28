<?php

use App\Models\Employee;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-12 08:00'));

    $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $this->hair = ServiceCategory::create(['name' => 'Fryzjer', 'description' => 'Opis', 'icon' => 'fa-solid fa-scissors']);
    $this->nails = ServiceCategory::create(['name' => 'Paznokcie', 'description' => 'Opis', 'icon' => 'fa-solid fa-spa']);

    $this->haircut = adminService($this->hair, ['name' => 'Strzyzenie damskie']);
    $this->manicure = adminService($this->nails, ['name' => 'Manicure hybrydowy', 'active' => 0]);
});

function adminService(ServiceCategory $category, array $attributes = []): Service
{
    return Service::forceCreate($attributes + [
        'uuid' => (string) Str::uuid(),
        'category_id' => $category->uuid,
        'name' => 'Usluga '.Str::random(6),
        'description' => 'Opis uslugi',
        'duration' => 60,
        'price' => 100,
        'active' => 1,
    ]);
}

function bookService(Service $service, string $date = '2026-10-13'): Reservation
{
    $uuid = (string) Str::uuid();
    $employee = Employee::factory()->create([
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true])->uuid,
    ]);

    DB::table('reservations')->insert([
        'uuid' => $uuid,
        'user_id' => User::factory()->create(['role' => 'client', 'active' => true])->uuid,
        'employee_id' => $employee->uuid,
        'service_id' => $service->uuid,
        'reservation_date' => $date,
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'confirmed',
        'price' => 100,
    ]);

    return Reservation::find($uuid);
}

function adminServices($test)
{
    return Livewire::actingAs($test->admin)->test('admin-services');
}

function newServiceForm($test)
{
    return adminServices($test)
        ->call('create')
        ->set([
            'categoryId' => $test->hair->uuid,
            'name' => 'Koloryzacja',
            'description' => 'Farbowanie całych włosów',
            'duration' => 90,
            'price' => '220,50',
        ]);
}

test('only admins can open the services tab', function (string $role) {
    Livewire::actingAs(User::factory()->create(['role' => $role, 'active' => true]))
        ->test('admin-services')
        ->assertForbidden();
})->with(['client', 'employee']);

test('lists every service', function () {
    adminServices($this)
        ->assertSee('Strzyzenie damskie')
        ->assertSee('Manicure hybrydowy')
        ->assertSee('Nieaktywna</span>', false);
});

test('filters by search, category and status', function (array $filters, string $expected) {
    $uuids = adminServices($this)->set($filters)->get('services')->pluck('uuid')->all();

    expect($uuids)->toBe([$this->{$expected}->uuid]);
})->with([
    'search' => [['search' => 'Manic'], 'manicure'],
    'category' => [fn () => ['category' => $this->hair->uuid], 'haircut'],
    'active' => [['status' => 'active'], 'haircut'],
    'inactive' => [['status' => 'inactive'], 'manicure'],
]);

test('changing a filter goes back to page 1', function () {
    Livewire::actingAs($this->admin)
        ->withQueryParams(['services_page' => 2])
        ->test('admin-services')
        ->assertSet('paginators.services_page', 2)
        ->set('search', 'Strzy')
        ->assertSet('paginators.services_page', 1);
});

test('admin adds a service', function () {
    newServiceForm($this)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false)
        ->assertSee('Koloryzacja');

    $service = Service::where('name', 'Koloryzacja')->first();
    expect($service)->not->toBeNull()
        ->and($service->uuid)->not->toBeEmpty()
        ->and($service->slug)->toBe('koloryzacja')
        ->and($service->category_id)->toBe($this->hair->uuid)
        ->and($service->duration)->toBe(90)
        ->and((float) $service->price)->toBe(220.5)
        ->and((int) $service->active)->toBe(1);
});

test('a new service starts in the filtered category', function () {
    adminServices($this)
        ->set('category', $this->nails->uuid)
        ->call('create')
        ->assertSet('categoryId', $this->nails->uuid);
});

test('admin edits a service and the slug follows the name', function () {
    adminServices($this)
        ->call('edit', $this->haircut->uuid)
        ->assertSet('name', 'Strzyzenie damskie')
        ->assertSet('categoryId', $this->hair->uuid)
        ->set('name', 'Strzyżenie męskie')
        ->set('categoryId', $this->nails->uuid)
        ->set('price', '150')
        ->set('active', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($this->haircut->fresh())
        ->name->toBe('Strzyżenie męskie')
        ->slug->toBe('strzyzenie-meskie')
        ->category_id->toBe($this->nails->uuid)
        ->and((float) $this->haircut->fresh()->price)->toBe(150.0)
        ->and((int) $this->haircut->fresh()->active)->toBe(0);
});

test('saving a service keeps its own name valid', function () {
    adminServices($this)
        ->call('edit', $this->haircut->uuid)
        ->set('description', 'Nowy opis')
        ->call('save')
        ->assertHasNoErrors();
});

test('service form is validated', function (string $field, $value, string $message) {
    newServiceForm($this)
        ->set($field, $value)
        ->call('save')
        ->assertHasErrors($field)
        ->assertSee($message);

    expect(Service::count())->toBe(2);
})->with([
    'no category' => ['categoryId', '', 'Wybierz kategorię'],
    'unknown category' => ['categoryId', 'nope', 'Wybrana kategoria nie istnieje'],
    'empty name' => ['name', '', 'Podaj nazwę usługi'],
    'name with the same slug' => ['name', 'Strzyżenie Damskie', 'Usługa o tej nazwie już istnieje'],
    'name without letters' => ['name', '!!!', 'Nazwa musi zawierać litery lub cyfry'],
    'empty description' => ['description', '', 'Podaj opis usługi'],
    'too short' => ['duration', 0, 'Czas trwania to od 5 do 480 minut'],
    'too long' => ['duration', 481, 'Czas trwania to od 5 do 480 minut'],
    'negative price' => ['price', '-1', 'Podaj cenę od 0 do 99999,99 zł'],
    'price with three decimals' => ['price', '10,123', 'Cena może mieć najwyżej dwa miejsca po przecinku'],
    'price not a number' => ['price', 'dużo', 'Podaj cenę od 0 do 99999,99 zł'],
]);

test('admin toggles a service on and off', function () {
    adminServices($this)->call('toggleActive', $this->haircut->uuid);
    expect((int) $this->haircut->fresh()->active)->toBe(0);

    adminServices($this)->call('toggleActive', $this->haircut->uuid);
    expect((int) $this->haircut->fresh()->active)->toBe(1);
});

test('deactivating warns about upcoming reservations and keeps them', function () {
    $reservation = bookService($this->haircut);

    adminServices($this)
        ->assertSee('Usługa ma 1 nadchodzących rezerwacji')
        ->call('toggleActive', $this->haircut->uuid);

    expect((int) $this->haircut->fresh()->active)->toBe(0)
        ->and($reservation->fresh()->status)->toBe('confirmed');
});

test('admin deletes a service without reservations and it is detached from employees', function () {
    $employee = Employee::factory()->create([
        'user_id' => User::factory()->create(['role' => 'employee', 'active' => true])->uuid,
    ]);
    $employee->services()->attach($this->haircut->uuid, ['uuid' => (string) Str::uuid()]);

    adminServices($this)
        ->call('delete', $this->haircut->uuid)
        ->assertDontSee('Strzyzenie damskie');

    expect(Service::find($this->haircut->uuid))->toBeNull()
        ->and(DB::table('employee_services')->where('service_id', $this->haircut->uuid)->exists())->toBeFalse();
});

test('a service with reservations cannot be deleted', function () {
    bookService($this->haircut, '2026-10-01');

    adminServices($this)
        ->call('delete', $this->haircut->uuid)
        ->assertForbidden();

    expect(Service::find($this->haircut->uuid))->not->toBeNull();
});

test('inactive services are hidden from clients', function () {
    Livewire::actingAs(User::factory()->create(['role' => 'client', 'active' => true]))
        ->test('show-services')
        ->call('categorySelected', 'Paznokcie')
        ->assertDontSee('Manicure hybrydowy')
        ->assertSee('Brak uslug w tej kategorii');
});

test('the admin menu shows the services tab', function () {
    Livewire::actingAs($this->admin)
        ->test('admin-menu')
        ->set('option', 'services')
        ->assertSeeLivewire('admin-services');
});

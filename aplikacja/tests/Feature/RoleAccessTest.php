<?php

use App\Models\User;

// Panel routes sit behind auth + verified + active, plus the `role` middleware (EnsureHasRole)

test('panels are restricted by role', function (string $role, int $adminPanel, int $employeePanel) {
    $user = User::factory()->create(['role' => $role, 'active' => true]);

    $this->actingAs($user)->get(route('dashboard.admin'))->assertStatus($adminPanel);
    $this->actingAs($user)->get(route('dashboard.employee'))->assertStatus($employeePanel);
})->with([
    'client' => ['client', 403, 403],
    'employee' => ['employee', 403, 200],
    'admin' => ['admin', 200, 200],
]);

test('every role can open the user dashboard', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'active' => true]);

    $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
})->with(['client', 'employee', 'admin']);

test('guests are sent to the login page', function (string $panel) {
    $this->get(route($panel))->assertRedirect(route('login'));

    $this->assertGuest();
})->with(['dashboard.admin', 'dashboard.employee']);

test('an inactive admin is logged out instead of seeing the admin panel', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => false]);

    $this->actingAs($admin)->get(route('dashboard.admin'))
        ->assertRedirect(route('account.inactive'));

    $this->assertGuest();
});

test('a forbidden panel shows the polish 403 page', function () {
    $client = User::factory()->create(['role' => 'client', 'active' => true]);

    $this->actingAs($client)->get(route('dashboard.admin'))
        ->assertForbidden()
        ->assertSee('Nie masz uprawnień, aby wyświetlić tę stronę.');
});

test('clients do not see links to the staff panels', function () {
    $client = User::factory()->create(['role' => 'client', 'active' => true]);

    $this->actingAs($client)->get(route('dashboard.index'))
        ->assertOk()
        ->assertDontSee(route('dashboard.admin'))
        ->assertDontSee(route('dashboard.employee'));
});

test('employees see a link to their panel but not to the admin panel', function () {
    $employee = User::factory()->create(['role' => 'employee', 'active' => true]);

    $this->actingAs($employee)->get(route('dashboard.index'))
        ->assertOk()
        ->assertSee(route('dashboard.employee'))
        ->assertDontSee(route('dashboard.admin'));
});

test('admins see a link to the admin panel', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    $this->actingAs($admin)->get(route('dashboard.index'))
        ->assertOk()
        ->assertSee(route('dashboard.admin'));
});

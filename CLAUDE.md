# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository layout

The Laravel application lives in `aplikacja/`. Run every command below from that directory. The repo root holds only docs (`README.md` and `AGENTS.md`, both in Polish) plus a few side folders (`resources/`, `szczegoly/`, `testfrontend/`) that are not part of the app.

## Commands

```bash
composer setup            # install deps, copy .env, key:generate, migrate, npm install, npm run build
composer dev              # serve + queue:listen + pail logs + vite, all together
php artisan serve         # just the server
npm run dev               # just Vite
php artisan migrate:fresh --seed   # rebuild the SQLite DB (database/database.sqlite) with seed data

php artisan test                          # full suite (Pest 3)
php artisan test --filter=ProfileTest     # one test file/class or one test name
php artisan test tests/Feature/ProfileTest.php

./vendor/bin/pint         # code style (Laravel Pint)
```

Tests run against in-memory SQLite (configured in `phpunit.xml`).

## Stack

- Laravel 12, PHP 8.2+, SQLite by default
- **Livewire 4** drives the interactive UI. Breeze provides auth scaffolding (`routes/auth.php`, `app/Http/Controllers/Auth`).
- Tailwind with Vite, plus Alpine.js. Note that `resources/views/dashboard/dashboard.blade.php` also loads the Tailwind CDN, Font Awesome and flatpickr from CDNs alongside `@vite`.
- UI text is in Polish.

## Architecture

### Primary keys are `uuid` columns
Every domain table uses `$table->uuid()->primary()`, which creates a column named `uuid`, not `id`. The models set `$primaryKey = 'uuid'`, `$incrementing = false` and `$keyType = 'string'`, and foreign keys reference `uuid`. When adding a relationship, pass the key names explicitly, as the existing models do. Only `User` uses the `HasUuids` trait. For the other models, check how the UUID gets populated (factory or seeder) before creating records in app code.

Domain model: `ServiceCategory` → `Service` ↔ `Employee` (pivot `employee_services`). `Employee` belongs to `User` and has many `EmployeeAvailability` rows (`day_of_week`, `specific_date`, `start_time`/`end_time`) and many `Reservation` rows. `Reservation` statuses are `pending`, `confirmed`, `completed` and `cancelled`, and the model provides scopes and Polish labels for them. `User.role` is `admin`, `employee` or `user`.

### Livewire single-file components (⚡ files)
The reservation flow is built from Livewire 4 single-file components in `resources/views/components/`. The `⚡name.blade.php` files hold the component class (`new class extends Component`). Most of them render a **separate** plain Blade view of the same name (for example, `⚡reserve-service.blade.php` renders `view('components.reserve-service')`). So the logic lives in the ⚡ file and the markup in the file without ⚡. Edit both when you change a component.

### Reservation flow (event-driven)
On `/dashboard` (`DashboardController@index`), the page mounts `@livewire('show-services')` and `@livewire('reserve-service')`. The components communicate only through dispatched events:

1. `service-category-card` → `categorySelected` → `show-services` loads the services in that category.
2. `category-service` → `serviceChosen` → `reserve-service` opens a modal and loads the employees with their availability.
3. Selecting an employee dispatches `employee-selected` (available weekdays) to `employee-week-calendar`, which dispatches `daySelected`.
4. `reserve-service` then dispatches `employee-work-time-data` and mounts `employee-hour-calendar`, which dispatches `hourSelected`.
5. `reserve()` is still a stub. `app/Livewire/Forms/ReservationForm.php` and `app/Policies/ReservationPolicy.php` are in-progress pieces for persisting reservations.

When you add a step, follow this pattern: dispatch an event and handle it with `#[On('...')]` or `$listeners`, instead of nesting component state.

### Routes
`routes/web.php` requires `auth.php` and `dashboard.php`. Dashboard routes use `auth` + `verified` middleware, the `/dashboard` prefix and the `dashboard.` name prefix.

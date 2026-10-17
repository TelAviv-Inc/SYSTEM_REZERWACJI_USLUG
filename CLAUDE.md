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
npm run format            # Prettier for resources/ (Blade via @shufo/prettier-plugin-blade, JS, CSS); format:check to only check
```

Pint formats `.php` files and skips `*.blade.php`. Prettier formats only the Blade, JS and CSS files, including the PHP class at the top of the ⚡ files. Its config is `.prettierrc.json`.

Tests run against in-memory SQLite (configured in `phpunit.xml`).

Testing gotchas:
- `UserFactory` picks a random `role` and sets `active` to false 20% of the time. A test that hits dashboard routes or Livewire components must pin `['active' => true]` (and a role if it matters). Otherwise `EnsureUserIsActive` logs the user out at random and the test is flaky.
- `layouts/guest.blade.php` prints `@yield('content')`, not a `$slot`. A view that still uses `<x-guest-layout>` renders an empty card with status 200, so a page test must assert the form fields (e.g. `assertSee('name="email"', false)`), not just the status. Forgot/reset password and verify-email extend `layouts/auth.blade.php` (the login-style card) and use the `x-auth-input` and `x-auth-submit` components.

## Stack

- Laravel 12, PHP 8.2+, SQLite by default
- **Livewire 4** drives the interactive UI. Breeze provides auth scaffolding (`routes/auth.php`, `app/Http/Controllers/Auth`).
- **Tailwind CSS v4** through `@tailwindcss/vite`, plus Alpine.js. There is no `tailwind.config.js` or `postcss.config.js`. Theme tokens (`--color-brand-*` and others) live in the `@theme` block of `resources/css/app.css`, and its `@source` lines set which files Tailwind scans. v3-only utilities such as `bg-opacity-*` and `ring-opacity-*` no longer exist, so use the slash syntax instead (`ring-green-500/50`). There is no Tailwind CDN. Font Awesome is still loaded from a CDN alongside `@vite`.
- Every dashboard page wraps its content in `<x-layouts.dashboard title="..." subtitle="...">` (`resources/views/components/layouts/dashboard.blade.php`), which holds the `<head>`, navigation, page heading and footer. Livewire injects its own scripts, so the pages don't use `@livewireStyles`/`@livewireScripts`.
- UI text is in Polish.

## Architecture

### Primary keys are `uuid` columns
Every domain table uses `$table->uuid()->primary()`, which creates a column named `uuid`, not `id`. The models set `$primaryKey = 'uuid'`, `$incrementing = false` and `$keyType = 'string'`, and foreign keys reference `uuid`. When adding a relationship, pass the key names explicitly, as the existing models do. Only `User` uses the `HasUuids` trait. For the other models, check how the UUID gets populated (factory or seeder) before creating records in app code.

Domain model: `ServiceCategory` → `Service` ↔ `Employee` (pivot `employee_services`). `Employee` belongs to `User` and has many `EmployeeAvailability` rows (`day_of_week`, `specific_date`, `start_time`/`end_time`) and many `Reservation` rows. `Reservation` statuses are `pending`, `confirmed`, `completed` and `cancelled`, and the model provides scopes and Polish labels for them. `User.role` is an enum of `client` (the default), `employee` or `admin`. `User.active` is a boolean flag (see `EnsureUserIsActive` under Routes).

### Livewire single-file components (⚡ files)
The reservation flow is built from Livewire 4 single-file components in `resources/views/components/`. The `⚡name.blade.php` files hold the component class (`new class extends Component`). Most of them render a **separate** plain Blade view of the same name (for example, `⚡reserve-service.blade.php` renders `view('components.reserve-service')`). So the logic lives in the ⚡ file and the markup in the file without ⚡. Edit both when you change a component.

### Reservation flow (event-driven)
On `/dashboard` (`DashboardController@index`), the page mounts `@livewire('show-services')` and `@livewire('reserve-service')`. The components communicate only through dispatched events:

1. `service-category-card` → `categorySelected` → `show-services` loads the services in that category.
2. `category-service` → `serviceChosen` → `reserve-service` opens a modal and loads the employees with their availability.
3. Selecting an employee dispatches `employee-selected` (available weekdays) to `employee-week-calendar`, which dispatches `daySelected`.
4. `reserve-service` then dispatches `employee-work-time-data` and mounts `employee-hour-calendar`, which dispatches `hourSelected`.
5. `reserve()` fills and validates `app/Livewire/Forms/ReservationForm.php`, then calls `ReservationForm::reserve()`. That method saves the reservation inside `DB::transaction`, locks the employee row with `lockForUpdate` and checks `Reservation::overlapping()`, so two users can't book the same slot. It sets the reservation's `uuid` with `Str::uuid()`. `app/Policies/ReservationPolicy.php` holds the create, view, cancel and confirm rules. Covered by `tests/Feature/ReservationTest.php`.

When you add a step, follow this pattern: dispatch an event and handle it with `#[On('...')]` or `$listeners`, instead of nesting component state.

### User profile
`/dashboard/profile` (`DashboardController@profile`, view `dashboard/user-profile`) mounts `profile-summary` and `user-profile-menu`. The menu switches between the `profile-information`, `profile-data`, `profile-change-password` and `profile-settings` tabs (deactivating and deleting the account live in settings). The form objects are `app/Livewire/Forms/ProfileDataForm.php` and `UpdatePasswordForm.php`. Phone numbers go through `App\Support\Phone::normalize()` at registration and in the profile.

### Routes
`routes/web.php` requires `auth.php` and `dashboard.php`. Dashboard routes use `auth` + `verified` + `active` middleware, the `/dashboard` prefix and the `dashboard.` name prefix.

`active` is the alias for `App\Http\Middleware\EnsureUserIsActive`, registered in `bootstrap/app.php`. It logs out users whose `active` is false and redirects them to `account.inactive` (`/konto-nieaktywne`). `AppServiceProvider` also registers it as Livewire persistent middleware, so it runs on Livewire update requests as well.

`role` is the alias for `App\Http\Middleware\EnsureHasRole` (`role:admin`, `role:employee,admin`), which returns 403 for other roles. It guards `/dashboard/admin` and `/dashboard/employee` and is Livewire persistent middleware too. Covered by `tests/Feature/RoleAccessTest.php`.

### Rate limiting
`bootstrap/app.php` appends `throttle:web` to the whole `web` group. `AppServiceProvider::boot()` defines the named limiters:
- `web`: 120 requests/min per user (or IP for guests). Livewire update requests get a separate 300/min bucket. They are matched by the route name `*livewire.update`, not by the `X-Livewire` header, so the header can't be spoofed to get the higher limit.
- `auth`: 10 requests/min with a separate counter per path. Used as `throttle:auth` in `routes/auth.php` on the register, login, forgot-password and reset-password POSTs and on PUT password.

Covered by `tests/Feature/RateLimitTest.php`.

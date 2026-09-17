# Agent Instructions for System Rezerwacji Usług

## Repository Structure
- Application code is in the `aplikacja/` subdirectory
- Main Laravel application with standard structure (app/, config/, database/, resources/, routes/)

## Key Commands
- **Setup**: `php artisan setup` (installs dependencies, generates key, migrates, installs npm packages, builds assets)
- **Development Server**: `php artisan serve`
- **Vite Development**: `npm run dev`
- **Running Tests**: `php artisan test` or `pest`
- **Database Migrations**: `php artisan migrate` 
- **Database Rollback**: `php artisan migrate:rollback`

## Framework Details
- Laravel 12.x with PHP 8.2+
- Uses SQLite by default (configurable)
- Uses UUIDs as primary keys for models (User, Service, Employee, EmployeeAvailability, ServiceCategory)
- Uses Tailwind CSS for styling with Vite for asset compilation
- Authentication system with User model and roles

## Notable Features
- Service categories with icons
- User role-based access control (admin, employee, user)
- Dashboard view showing service categories
- Components such as navigation and service category cards
- Seeders populate initial data including 10 users and 4 service categories

## Testing Setup
- Tests use PHPUnit or PestPHP
- Testing environment uses in-memory SQLite database (`:memory:`)
- Configuration in `phpunit.xml` sets appropriate testing env vars
- Test suite includes both Unit and Feature tests

## Architecture Notes
- Models implement HasUuids trait for UUID support
- Routes are defined in `routes/web.php`
- Authentication middleware applied to dashboard route
- Blade templates in `resources/views/`
- Component-based UI in `resources/views/components/`
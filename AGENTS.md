# Repository Guidelines

## Project Structure & Module Organization
This project follows a **Service-Oriented Architecture** on top of **Laravel 12**.
- **Service Layer**: Core business logic is encapsulated in `app/Services/` using a pattern of `*Service.php` implementing a `*ServiceInterface.php`. Each service is typically grouped by domain (e.g., `Auth`, `Cashier`, `Payments`, `Settings`).
- **Enums**: Application-wide constants and statuses are defined in `app/Enums/`.
- **Frontend**: Assets are managed by **Vite** with **Tailwind CSS 4.0** support. Entry points are in `resources/js/app.js` and `resources/css/app.css`.
- **Database**: Migrations, factories, and seeders follow standard Laravel conventions in `database/`.

## Build, Test, and Development Commands
Manage the environment using `composer` and `npm`:
- **Development (Unified)**: `composer dev` – Runs the Laravel server, queue listener, log tailing, and Vite dev server concurrently.
- **Frontend Only**: `npm run dev` for HMR, or `npm run build` for production assets.
- **Database Setup**: `php artisan migrate --seed` to initialize the schema and populate default data.
- **Testing**: `php artisan test` or `composer test` to run the PHPUnit suite. Use `php artisan test --filter Name` to run a specific test.

## Coding Style & Naming Conventions
- **PHP**: Follows **PSR-12** standards. Code formatting is enforced via **Laravel Pint** (`vendor/bin/pint`).
- **JavaScript**: Managed via Vite.
- **Naming**: Use camelCase for variables/methods and PascalCase for Classes/Interfaces in PHP. Interfaces must be postfixed with `Interface` (e.g., `AuthServiceInterface`).

## Testing Guidelines
- **Framework**: **PHPUnit** is the primary testing tool.
- **Organization**: Tests are located in `tests/Unit/` for isolated logic and `tests/Feature/` for HTTP/integration tests.
- **Requirements**: Ensure all new services have corresponding tests. Mock external services like Midtrans using Mockery where appropriate.

## Commit & Pull Request Guidelines
Follow conventional commit prefixes:
- `feat:` for new functionality.
- `fix:` for bug fixes.
- `refactor:` for code changes that neither fix a bug nor add a feature.
- `test:` for adding or updating tests.
- `chore:` for updating build tasks, package manager configs, etc.

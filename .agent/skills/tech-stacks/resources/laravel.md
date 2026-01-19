# Tech Stack: Laravel (Modular Monolith)

## 1. Core Stack
- **Framework**: Laravel 12.x
- **Language**: PHP 8.4+
- **Architecture**: Modular Monolith (Custom `modules/` directory)
- **Database**: PostgreSQL / MySQL 8.0+
- **API Standard**: JSON:API

## 2. Architectural Guidelines
- **Modules**:
  - Located in `/modules/{ModuleName}`.
  - Each module has its own `composer.json` (merged via `wikimedia/composer-merge-plugin`).
  - Structure per module:
    - `src/Http/Controllers`
    - `src/Models`
    - `src/Services` (Business Logic)
    - `routes/api.php`
- **Pattern**: Service-Repository or Service-Action.
- **DTO**: Use `spatie/laravel-data` for strict typing.

## 3. Coding Standards
- **Strict Types**: `declare(strict_types=1);` mandatory.
- **Dependency Injection**: Constructor injection.
- **Testing**: pestphp/pest (Feature tests per module).

## 4. Folder Structure (Root)
- `/app`: Core infrastructure / Shared.
- `/modules`: Feature modules (Auth, Finance, Inventory, etc).
- `/check`: Custom scripts (e.g. `worker.queue.sh`).


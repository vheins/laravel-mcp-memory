# Tech Stack: Laravel Filament (ERP Monolith)

## 1. Core Stack
- **Framework**: Laravel 12.x
- **Admin Panel**: Filament 4.x
- **Frontend**: Livewire 4.x
- **Architecture**: Standard Monolith (ERP Optimized)
- **Permissions**: `spatie/laravel-permission` + `bezhansalleh/filament-shield`
- **Database**: PostgreSQL / MySQL 8.0+

## 2. Architectural Guidelines
- **Structure**:
  - `/app/Filament`: All Admin Logic.
  - `/app/Filament/Clusters`: Group related Resources (e.g. `Finance`, `HR`).
  - `/app/Filament/Resources`: Standalone Resources.
  - `/app/Models`: Eloquent Models.
  - `/app/Providers`: App Service Providers (FilamentPanelProvider).
  - `/app/Policies`: Authorization Policies (Strict).
- **Pattern**: Resource-driven development. Logic resides in Resources and Actions.

## 3. Coding Rules
- **Themes**: Custom theme at `resources/css/filament/dashboard/theme.css`.
- **Optimization**: Use `php artisan filament:optimize` in production.
- **Strict Mode**: `Model::shouldBeStrict()` in local environment.

## 4. Testing
- **Feature**: Test Filament Resources (`$this->get(UserResource::getUrl('index'))->assertSuccessful()`).
- **Unit**: Test complex actions/jobs.


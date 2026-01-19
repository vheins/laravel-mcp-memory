# Tech Stack: Nuxt.js (Modular)

## 1. Core Stack
- **Framework**: Nuxt 4.x
- **Language**: TypeScript
- **State**: Pinia (Setup Store Standard)
- **UI**: Nuxt UI (Tailwind CSS)
- **Architecture**: Modular (Nuxt Layers / Local Modules)

## 2. Architectural Guidelines
- **Modules**:
  - Located in `/modules/{ModuleName}`.
  - Each module is a "Mini Nuxt App" (Layer) or contains `pages`, `components`, `composables`.
  - Registered in `nuxt.config.ts`.
- **Aliases**:
  - `#/`: Mapped to `/modules/`.
  - `~/`: Mapped to `/`.

## 3. Coding Rules
- **Data Fetching**: Use `useFetch` with typed responses.
- **State**: Pinia Stores inside modules (`modules/Auth/stores/useAuth.ts`).
- **Middleware**: Module-specific middleware in `modules/{ModuleName}/middleware`.

## 4. Folder Structure
- `/modules`: Feature modules.
- `/server`: Global API routes.
- `/layouts`: App-wide layouts.
- `/components`: App-wide / UI Kit components.


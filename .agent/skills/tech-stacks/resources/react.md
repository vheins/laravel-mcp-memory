# Tech Stack: React 19 (Vite)

## 1. Core Stack
- **Build Tool**: Vite
- **Language**: TypeScript 5.x
- **Styling**: Tailwind CSS / CSS Modules
- **State**: TanStack Query (Server) + Zustand/Context (Client)
- **Routing**: React Router DOM 6+

## 2. Coding Rules
- **Hooks**: Use custom hooks for logic separation (`useAuth`, `useCart`).
- **Components**: Functional Components only. Avoid `useEffect` for derived state.
- **Performance**: Use `useMemo` and `useCallback` for expensive operations.
- **Imports**: Absolute imports `@/components/...` configured in `tsconfig.json`.

## 3. Architecture
- **Structure**:
  - `/src/components`: Reusable UI components.
  - `/src/pages`: Page components (routed).
  - `/src/features`: Feature-based modules (api, hooks, components, types).
  - `/src/lib`: Axios instance, utils.

## 4. Testing
- **Unit**: Vitest + React Testing Library.
- **E2E**: Cypress or Playwright.

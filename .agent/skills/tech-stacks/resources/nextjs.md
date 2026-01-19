# Tech Stack: Next.js (App Router)

## 1. Core Stack
- **Framework**: Next.js 16+ (App Router)
- **Language**: TypeScript 5.x
- **Styling**: Tailwind CSS
- **State**: Server State (TanStack Query) + Client State (Zustand)

## 2. Coding Rules
- **Server Components**: Default to RSC. Use `"use client"` sparingly.
- **Data Fetching**: Use `fetch` in Server Components or Server Actions.
- **Forms**: Use `react-hook-form` + `zod` validation.
- **UI Library**: Shadcn UI (Radix Primitives).

## 3. Architecture
- **Structure**:
  - `/app`: Pages and Layouts.
  - `/components/ui`: Atomic design components.
  - `/lib`: Utilities and Server Actions.
- **Naming**: `PascalCase` for Components, `kebab-case` for files.

## 4. Testing
- **E2E**: Playwright.
- **Unit**: Vitest + React Testing Library.

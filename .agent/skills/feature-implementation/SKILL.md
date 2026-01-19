---
name: feature-implementation
description: Skill for implementing features based on documentation, bridging technical specs (API, ERD, Stories) and code using a Test-Driven Approach.
---

# Feature Implementation Skill

You are a Senior Full-stack Developer. This skill guides you in transforming documentation into working, high-quality code while maintaining strict synchronization with the project's technical specifications.

## Core Principles
1.  **Documentation First**: Never write code without reading the corresponding Feature Doc, API Spec, and Test Scenarios.
2.  **Naming Consistency**: Database fields, API endpoints, JSON keys, and variables MUST match the documentation exactly.
3.  **TDD (Test-Driven Development)**: Write or update tests before implementing the logic.

## Workflow

### 1. Context Loading
- **Modules**: Locate the module in `.agent/documents/application/modules/`.
- **Logic**: Research Business Rules and Mermaid ERDs.
- **Contract**: Check `.agent/documents/application/api/` for the JSON:API contract.

### 2. Red Step: Design-First Testing
- Create or update the test suite (e.g., Pest for Laravel, Vitest for Vue).
- Focus on the "Happy Path" defined in the Test Scenarios.
- Run the test and ensure it fails (Red).

### 3. Green Step: Implementation
- **Data Layer**: Create migrations and models based on the ERD.
- **Service Layer**: Implement the business logic (Backend Services or Frontend Stores).
- **API Layer**: Build controllers and routes. Ensure JSON:API compliance.
- **UI Layer**: Implement components and pages.

### 4. Verification
- Run the test suite and ensure it passes (Green).
- Perform a manual verification of the UI and edge cases.
- Run linting and formatting.

## Best Practices
- **Atomic Commits**: Commit each task separately with the format: `feat(<module>): <description> (<TASK-ID>)`.
- **Strict Typing**: Use type hints and interfaces to enforce the contract defined in documentation.
- **No Shortcuts**: If a requirement in the documentation seems wrong, update the documentation FIRST before changing the code.

## References
- Templates: `.agent/documents/templates/`
- Tasks: `.agent/tasks/implementation-tasks.md`

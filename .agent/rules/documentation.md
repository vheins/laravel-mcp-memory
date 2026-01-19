---
trigger: always_on
---

# Documentation & Implementation Rules

You are the guardian of this project's quality. Adhere to these constraints strictly.

## 1. Core Principles
- **No Gap Policy**: Every User Story must have an API Endpoint and a Test Scenario.
- **Traceability**: Task IDs must follow `[MOD]-BE-[XX]` and `[MOD]-FE-[XX]`.
- **Synchronization**: Use the "Change One, Check All" principle across Module, API, and Testing docs.
- **Source of Truth**: The Feature Documentation (`implementation tasks` table) is the source of truth for `.agent/tasks/implementation-tasks.md`.

## 2. Structure Standards
- **Modules**: `.agent/documents/application/modules/<module>/[overview|feature].md`
- **API**: `.agent/documents/application/api/<module>/api-<module>.md` (JSON:API mandatory)
- **Testing**: `.agent/documents/application/testing/<module>/test-<module>.md`

## 3. Skills Delegation (CRITICAL)
Detailed execution strategy and formats are managed via skills. You MUST leverage them:
- **Expert Documentation**: Use for User Story formats, JSON:API standards, and ERD requirements.
- **QA Design & Execution**: Use for designing/running Positive, Negative, Monkey, and Security tests.
- **Feature Implementation**: Use for the TDD-based coding workflow.
- **Tech Stacks**: Use for architecture-specific patterns (Laravel, Vue, etc.).

## 4. Operational Rules
- **Templates**: Always use base templates in `.agent/documents/templates/`.
- **Research First**: Never guess business logic; search the codebase or documentation.
- **Update Tasks**: Mark tasks as `Done` in `implementation-tasks.md` immediately upon completion.

FAILURE TO FOLLOW THESE RULES will result in documentation drift and is unacceptable.
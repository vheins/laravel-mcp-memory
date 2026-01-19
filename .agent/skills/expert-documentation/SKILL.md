---
name: expert-documentation
description: A skill for generating and maintaining high-quality documentation following established standards (JSON:API, Mermaid ERDs, Positive/Negative testing).
---

# Expert Documentation Skill

You are an expert technical writer and systems architect. This skill guides you in creating comprehensive, professional documentation that ensures no gaps between user stories, APIs, and tests.

## Standards & Principles

### 1. The "No Gap" Policy
Every User Story must have a corresponding API Endpoint, and every API Endpoint must have a Test Scenario.

### 2. File Structure
All documentation must be placed in the following directory structure:
- **Module Overview**: `.agent/documents/application/modules/<module>/overview.md`
- **Feature Logic**: `.agent/documents/application/modules/<module>/<feature>.md` (For complex features)
- **API Specification**: `.agent/documents/application/api/<module>/api-<module>.md`
- **Testing Plan**: `.agent/documents/application/testing/<module>/test-<module>.md`

### 3. Documentation Requirements

#### Feature Documentation
- **User Stories**: Use the format: `As a [user], I want to [action], so that [benefit]`.
- **Acceptance Criteria**: Explicit list of testable requirements.
- **Data Model**: MUST include a Mermaid ERD showing relationships.

#### API Documentation (JSON:API)
- **Compliance**: Follow [JSON:API](https://jsonapi.org) standards strictly.
- **Paths**: Use `kebab-case` for URLs.
- **Details**: Provide explicit Request/Response bodies (JSON) and error cases (4xx, 5xx).

#### Testing Documentation
- **Paths**: Cover Positive, Negative (validation/errors), Monkey (fuzzing), and Security (authorization/IDOR).
- **Priority**: Assign `High`, `Medium`, or `Low` to every scenario.

## Workflows

### Creating New Documentation
1. **Analyze**: Identify if the module is Global (standard best practices) or Specific (requires codebase research).
2. **Research**: Use `find_by_name` and `grep_search` to find existing models/controllers for specific modules.
3. **Draft**: Use templates from `.agent/documents/templates/`.
4. **Link**: Ensure all documents are cross-linked (Overview links to Feature/API/Testing).

### Updating Existing Documentation
1. **Change One, Check All**: If logic changes, update API and Testing documents immediately.
2. **Sync Tasks**: Update `.agent/tasks/implementation-tasks.md` to match any changes in the granular implementation tasks.

## Best Practices
- **Never Guess**: If unsure about business logic, research the code or ask the user.
- **Visuals**: Use Mermaid diagrams for complex state machines or flows.
- **Conciseness**: Write for busy developers—be clear and structured.

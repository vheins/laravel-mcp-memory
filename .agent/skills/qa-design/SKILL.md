---
name: qa-design
description: Skill for designing structured test flows and scenarios (Positive, Negative, Monkey, Security).
---

# QA Design Skill

You are a Senior QA Engineer. This skill guides you in designing comprehensive test scenarios specifically tailored for this project's standards.

## Core Objectives
- Ensure 100% coverage of User Stories.
- Identify edge cases early through Monkey Testing.
- Secure the application via role-based Security Testing.

## Test Categories

### 1. Functional Testing (Positive & Negative)
- **Positive**: Validating that the "Happy Path" works as intended.
- **Negative**: Ensuring the system handles invalid data, validation errors, and boundary conditions gracefully.

### 2. Monkey Testing (Chaos/Exploratory)
- Focus on random interactions, high-frequency clicks, and unconventional input sequences.
- Look for state management issues and race conditions.

### 3. Security Testing
- **AuthZ/AuthN**: Verify that users can only access what they are permitted to.
- **IDOR**: Check for Indirect Object Reference vulnerabilities.
- **Validation**: Ensure no harmful scripts can be injected.

## Workflow
1. **Prequisites**: Read the Feature Documentation and User Stories.
2. **Apply Template**: Use `.agent/documents/templates/testing-feature.md`.
3. **Draft Scenarios**: Create a breakdown for each category (Functional, Monkey, Security).
4. **Prioritize**: Mark each case as `High`, `Medium`, or `Low`.
5. **Cross-Reference**: Link the test plan in the Module Overview.

## Guidelines
- **Atomic Tests**: Keep each case focused on a single behavior.
- **Clear Expectations**: "Expected Result" must be specific and verifiable.
- **No Overlap**: Avoid redundant tests that cover the same logic.

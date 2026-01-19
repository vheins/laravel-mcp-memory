# Implementation Tasks — MCP Memory

> Daftar tugas terperinci untuk implementasi modul MCP Memory.

---

## 1. Backend Implementation

| Task ID       | Component  | Status | Description                                                                                        |
| :------------ | :--------- | :----- | :------------------------------------------------------------------------------------------------- |
| **MEM-BE-01** | Migration  | Todo   | Create tables: `memories`, `repositories`, `audit_logs`. Index on `repository_id` & `type`.        |
| **MEM-BE-02** | Model      | Todo   | Setup Model `Memory` with Scopes (`RepositoryScope`) & Relations. Implement `Auditable`.           |
| **MEM-BE-03** | Service    | Todo   | Implement `MemoryService`: Logic for hierarchy resolution, collision handling, and search ranking. |
| **MEM-BE-04** | MCP API    | Todo   | Implement JSON-RPC Controller for `memory.write`, `memory.read`, `memory.search`.                  |
| **MEM-BE-05** | Validation | Todo   | Implement Rules: `ImmutableTypeRule` (prevent AI editing locked types).                            |
| **MEM-BE-06** | Seeder     | Todo   | Seed default System Constraints and Demo Repository.                                               |
| **MEM-BE-07** | Tests      | Todo   | Unit Test for Scope Hierarchy fallback. Feature Test for API isolation.                            |

---

## 2. Frontend Implementation (Filament)

| Task ID       | Component  | Status | Description                                                                   |
| :------------ | :--------- | :----- | :---------------------------------------------------------------------------- |
| **MEM-FE-01** | Resource   | Todo   | Create `MemoryResource`. Columns: Content (trunc), Type, Scope Badge, Status. |
| **MEM-FE-02** | Filters    | Todo   | Add Global Filter / Table Filter for `Repository`.                            |
| **MEM-FE-03** | Actions    | Todo   | Custom Actions: "Lock Memory", "Verify Memory".                               |
| **MEM-FE-04** | Widget     | Todo   | Memory Stats Widget (Total verified vs unverified per Repo).                  |
| **MEM-FE-05** | History UI | Todo   | Integrate `activitylog` timeline view into ViewRecord page.                   |

---

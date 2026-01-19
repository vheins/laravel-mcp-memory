# [Feature Name]

> Gunakan template ini untuk mendokumentasikan spesifikasi detail dari setiap FITUR dalam modul secara mendalam.

---

## Header & Navigation

- [Back to Module Overview](./overview.md)
- [Link to API Specification](../../api/<module-name>/api-<module-name>.md)
- [Link to Testing Scenario](../../testing/<module-name>/test-<module-name>.md)

---

## 1. Feature Overview

- **Brief Description:** Deskripsi teknis yang padat dan jelas. Gunakan istilah baku (*lifecycle*, bukan siklus hidup).
- **Role in Module:** Jelaskan posisi fitur ini dalam arsitektur modul (e.g., *authoritative source*, *aggregator*).
- **Business Value:** Fokus pada manfaat operasional, keamanan, atau auditability yang konkret.

---

## 2. User Stories

### US-[ID] — [Title]

**Sebagai** [Role]
**Saya ingin** [Goal]
**Sehingga** [Benefit]

**Acceptance Criteria:**

* [Criteria 1]
* [Criteria 2]
* [Criteria 3]

---

## 3. Business Flow & Rules

### 3.1 Business Flow
- **Mandatory:** Sertakan diagram alur menggunakan Mermaid (Sequence/Flowchart).

### 3.2 Business Rules
- Validasi input.
- Batasan domain.

---

## 4. Data Model

- Entitas yang terlibat.
- Relasi spesifik fitur ini.
- **Mandatory:** Sertakan ERD (Entity Relationship Diagram) menggunakan Mermaid code block untuk menggambarkan relasi antar entitas.

---

## 5. Compliance & Audit

- Aturan audit trail khusus fitur ini.
- Kebutuhan regulasi.

---

## 6. Implementation Tasks

> **Must Include:** Granular tasks for Migration, Seeder, Model, Service, Controller, Tests, and UI Components.

### 6.1 Backend

| Task ID     | Component  | Status | Description                           |
| :---------- | :--------- | :----- | :------------------------------------ |
| [MOD]-BE-01 | Migration  | Todo   | Create tables.                        |
| [MOD]-BE-02 | Seeder     | Todo   | Create seeders.                       |
| [MOD]-BE-03 | Model      | Todo   | Setup Model & Relations.              |
| [MOD]-BE-04 | Repository | Todo   | Implement Repository Pattern (files). |
| [MOD]-BE-05 | Service    | Todo   | Implement Business Logic.             |
| [MOD]-BE-06 | Controller | Todo   | Implement JSON:API Controller.        |
| [MOD]-BE-07 | Tests      | Todo   | Create Feature Tests (100% Coverage). |

### 6.2 Frontend

| Task ID     | Component   | Status | Description                        |
| :---------- | :---------- | :----- | :--------------------------------- |
| [MOD]-FE-01 | State       | Todo   | Setup Store.                       |
| [MOD]-FE-02 | API         | Todo   | Create Service/Axios Wrapper.      |
| [MOD]-FE-03 | Components  | Todo   | Create UI Components (Form/Table). |
| [MOD]-FE-04 | Pages       | Todo   | Assemble Pages.                    |
| [MOD]-FE-05 | Integration | Todo   | Connect & Handle Errors.           |

---

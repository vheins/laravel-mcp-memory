# [Module Name]

> Gunakan template ini sebagai halaman depan (landing page) untuk setiap Modul. Berisi ringkasan level tinggi dan daftar fitur.

---

## Header & Navigation

- [Back to Module List](../../../README.md)
- [Link to Testing Scenario](../../testing/<module-name>/test-<module-name>.md)

---

## 1. Module Introduction

### 1.1 Brief Description
[Jelaskan apa tanggung jawab utama modul ini dalam 1-2 kalimat]

### 1.2 Position & Role
- **Type:** [Core / Support / Optional]
- **Value:** [Nilai bisnis utama]

---

## 2. Feature List
 
 Modul ini terdiri dari fitur-fitur berikut. Silakan klik nama fitur untuk melihat spesifikasi detail ("User Stories" dan "Acceptance Criteria").
 
 | Fitur               | Deskripsi                                    | Sub-Modul                         |
 | :------------------ | :------------------------------------------- | :-------------------------------- |
 | **[Feature Name]**  | Penjelasan singkat fitur dan value utamanya. | [Feature File](./feature-file.md) |
 | **User Management** | CRUD user, aktivasi, dan assignment role.    | [User Mgmt](./user-management.md) |
 
 ---
 
 ## 3. High-Level Architecture
 
 Gambaran bagaimana modul ini berinteraksi dengan modul lain secara global, serta interaksi internal antar komponen.
 
 ```mermaid
 flowchart TB
     User["User / Client"]
     
     subgraph This_Module ["This Module (Scope)"]
         direction TB
         Service["Core Service<br>(Business Logic)"]
         Store["Data Store<br>(State Mgmt)"]
         
         Service -->|Process| Store
     end
 
     DB[(Module Database)]
     extService["External Service"]
     
     User -->|1. Request| Service
     Store --> DB
     Service -->|2. Sync| extService
 ```

---

## 4. Global Dependencies

- **Database:** [Nama DB]
- **Services:** [Service lain yang dibutuhkan]

---

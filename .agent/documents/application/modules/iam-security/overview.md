# Module Overview: IAM & Security

> Modul fondasi untuk identitas, otentikasi, dan otorisasi.

---

## Header & Navigation

- [Back to Module List](../../../README.md)
- [Link to Testing Scenario](../../testing/iam-security/test-authentication.md)

---

## 1. Module Introduction

### 1.1 Brief Description
Modul IAM & Security bertanggung jawab atas manajemen identitas pengguna, otentikasi (verifikasi identitas), dan otorisasi (hak akses) dalam sistem.

### 1.2 Position & Role
- **Tipe:** Core Module.
- **Value:** Melindungi data bisnis dan memastikan hanya pihak berwenang yang memiliki akses.

---

## 2. Feature List
 
 | Fitur               | Deskripsi                                                                                         | Sub-Modul                                            |
 | :------------------ | :------------------------------------------------------------------------------------------------ | :--------------------------------------------------- |
 | **Authentication**  | Layanan identitas lengkap mencakup Login, Register, MFA, dan manajemen sesi berbasis Token (JWT). | [Authentication](./authentication.md)                |
 | **User Management** | Manajemen lifecycle pengguna (Onboarding sampai Offboarding), profil, dan audit aktivitas akun.   | [User Management](./user-management.md)              |
 | **RBAC Engine**     | Kontrol akses granular berbasis Peran (*Role*) dan Izin (*Permission*) yang dinamis.              | [Role & Permission](./role-permission-management.md) |
 
 ---
 
 ## 3. High-Level Architecture
 
 ```mermaid
 flowchart TB
     User["User / Client"]
     
     subgraph IAM_Module ["IAM & Security Module"]
         direction TB
         Auth["Auth Service<br>(Login, Token Issue)"]
         UserDir["User Directory<br>(Profile, Credential Store)"]
         RBAC["RBAC Engine<br>(Policy Enforcement)"]
         
         Auth -->|Verify Creds| UserDir
         Auth -->|Get Permissions| RBAC
     end
 
     DB[(IAM Database)]
     
     User -->|1. Login| Auth
     UserDir --> DB
     RBAC --> DB
     
     Other["Other Modules"] -->|2. Validate Token| Auth
 ```

---

## 4. Global Dependencies

- **Database:** PostgreSQL (Users, Roles tables).
- **Services:** Email Service (untuk reset password).
- **Global Config:** JWT Secrets, Token TTL.

---

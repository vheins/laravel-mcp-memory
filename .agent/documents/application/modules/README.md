# Application Modules

Direktori ini berisi dokumentasi spesifikasi fungsional dan teknis untuk setiap modul dalam aplikasi.

## Struktur Direktori

Setiap sub-folder merepresentasikan satu domain atau modul bisnis:

- **iam-security/**: Identity & Access Management (Auth, Users, Roles).
- **configuration/**: System Configuration & Feature Flags.
- **notification/**: Notification Engine (Email, Push, In-App).
- *[Modul lainnya sesuai pengembangan]*

## Isi Dokumentasi Module

Setiap folder modul biasanya terdiri dari:

1.  **Overview (`overview.md`)**: Ringkasan modul, ERD Global, dan dependensi.
2.  **Feature Specs (`<feature-name>.md`)**: Detail logika bisnis, User Stories, dan Diagram Alur (Mermaid) untuk fitur spesifik.

## Template

Gunakan template standar saat membuat dokumentasi modul baru:
[Module Documentation Template](../../templates/module-documentation.md)

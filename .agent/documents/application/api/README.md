# API Specifications

Direktori ini berisi spesifikasi kontrak API (REST) untuk setiap modul aplikasi.

## Tujuan
Memberikan panduan yang jelas bagi Frontend, Mobile, dan 3rd party integrators mengenai endpoint, format request/response, dan kode error.

## Struktur Direktori

Pengelompokan folder mengikuti struktur modul aplikasi:

- **iam-security/**: Endpoint untuk Auth, User Management, RBAC.
- **configuration/**: Endpoint untuk Public Config, Admin Config.
- **notification/**: Endpoint untuk Send Notif, List Notif.

## Format Standar

Dokumen API sebaiknya mencakup:
1.  **Method & Endpoint**: `POST /api/v1/resource`
2.  **Request Headers**: Authentication, Content-Type.
3.  **Request Body**: JSON Schema / Table.
4.  **Response Examples**: Success (200/201) dan Error (4xx/5xx).

## Template

Gunakan template ini untuk membuat spesifikasi API baru:
[API Documentation Template](../../templates/api-documentation.md)

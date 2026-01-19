# Testing Documentation

Direktori ini berisi rencana pengujian (Test Plans) dan skenario pengujian (Test Scenarios) untuk memvalidasi kualitas perangkat lunak.

## Strategi Pengujian

Dokumentasi ini mencakup berbagai level pengujian:

1.  **Positive Cases (Happy Paths):** Memastikan fitur berjalan lancar sesuai alur bisnis normal.
2.  **Negative Cases:** Memastikan sistem menangani input tidak valid dan error dengan benar.
3.  **Monkey Tests (Chaos Engineering):** Menguji ketahanan sistem terhadap input acak dan load tinggi.
4.  **Security Testing:** Validasi celah keamanan (Authorization, Input Sanitization).
5.  **Frontend & E2E:** Skenario pengujian dari sisi antarmuka pengguna.

## Struktur Direktori

Sama seperti modul dan API, folder dikelompokkan berdasarkan domain:

- **iam-security/**: Test cases untuk Login, Register, User CRUD.
- **configuration/**: Test cases untuk Dynamic Config, Feature Flags.
- **notification/**: Test cases untuk Queue, Email Sending, Template rendering.

## Template

Setiap dokumen testing harus mengikuti template standar:
[Testing Documentation Template](../../templates/testing-documentation.md)

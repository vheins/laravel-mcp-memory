# Dokumentasi Aplikasi

Selamat datang di pusat dokumentasi teknis aplikasi. Direktori ini berisi detail implementasi, spesifikasi, dan pengujian untuk setiap modul dalam sistem.

## Struktur Direktori

Dokumentasi dibagi menjadi tiga bagian utama:

1.  **[Modules (Modul)](./modules/README.md)**
    *   Berisi ikhtisar fungsional, logika bisnis, aturan domain, dan arsitektur per modul.
    *   Mulai dari sini untuk memahami *apa* yang dilakukan setiap fitur.

2.  **[API Specifications (Spesifikasi API)](./api/README.md)**
    *   Berisi kontrak teknis endpoint, format request/response, dan kode error.
    *   Mengikuti standar JSON:API.

3.  **[Testing (Pengujian)](./testing/README.md)**
    *   Berisi strategi pengujian, skenario positif/negatif, dan monkey testing.
    *   Panduan QA dan Automated Testing.

## Indeks Modul

Berikut adalah daftar modul yang tersedia saat ini:

| Nama Modul           | Kode (Slug)        | Deskripsi Singkat                                            |
| :------------------- | :----------------- | :----------------------------------------------------------- |
| **IAM & Security**   | `iam-security`     | Otentikasi, Manajemen User, Role & Permission (RBAC).        |
| **Background Jobs**  | `background-jobs`  | Pemrosesan antrian asinkron dan cron jobs (Redis/Bull).      |
| **Taxonomy**         | `taxonomy`         | Klasifikasi polimorfik (Kategori, Tag, Label) yang terpusat. |
| **Media Management** | `media-management` | Layanan upload dan penyimpanan file terpusat (S3/Local).     |

---

[Kembali ke Root Dokumentasi](../README.md)
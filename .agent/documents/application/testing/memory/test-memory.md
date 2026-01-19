# Testing Scenarios — MCP Memory

> Dokumen ini merincikan skenario pengujian untuk memastikan integritas data, isolasi scope, dan kepatuhan aturan bisnis pada modul Memory.

---

## Header & Navigation

- [Back to Feature Doc](../../application/modules/memory/feature.md)
- [Back to API Spec](../../api/memory/api-memory.md)

---

## 1. Positive Testing
Memastikan fitur berjalan sesuai ekspektasi pada kondisi normal.

| ID          | Scenario                                             | expected Result                                               |
| :---------- | :--------------------------------------------------- | :------------------------------------------------------------ |
| TEST-POS-01 | Agent menyimpan memori dengan `repository_id` valid. | Status 201, memori tersimpan dengan status `unverified`.      |
| TEST-POS-02 | Agent mencari memori dengan query relevan.           | Mengembalikan list memori yang sesuai dengan query dan scope. |
| TEST-POS-03 | Admin memverifikasi memori via Filament.             | Status memori berubah dari `unverified` menjadi `verified`.   |
| TEST-POS-04 | Admin mengunci memori (`lock`).                      | Memori menjadi `Locked` dan tidak dapat diubah oleh AI.       |

---

## 2. Negative Testing
Memastikan sistem menangani input tidak valid atau aksi terlarang.

| ID          | Scenario                                                             | expected Result                            |
| :---------- | :------------------------------------------------------------------- | :----------------------------------------- |
| TEST-NEG-01 | Agent mencoba menulis memori tanpa `repository_id`.                  | Error 422 (Validation Error).              |
| TEST-NEG-02 | Agent mencoba menghapus memori berstatus `Locked`.                   | Error 403 (Forbidden).                     |
| TEST-NEG-03 | Pencarian memori dengan `repository_id` milik repo lain.             | Tidak mengembalikan data (Data Isolation). |
| TEST-NEG-04 | Admin mencoba mengunci memori yang bertentangan dengan Global Rules. | Peringatan validasi muncul di Dashboard.   |

---

## 3. Security & Access Testing
Memastikan keamanan data antar tenant.

| ID          | Scenario                                          | expected Result                                                            |
| :---------- | :------------------------------------------------ | :------------------------------------------------------------------------- |
| TEST-SEC-01 | Akses memori via API tanpa token valid.           | Error 401 (Unauthorized).                                                  |
| TEST-SEC-02 | Injeksi SQL pada query parameter `filter[query]`. | Sistem aman (Eloquent Parameter Binding).                                  |
| TEST-SEC-03 | Cross-Repository access attempt.                  | SQL query otomatis menyertakan `WHERE repository_id = ...` (Global Scope). |

---

## 4. Monkey Testing
Uji coba input acak atau perilaku tidak terduga.

- **Payload Bomb:** Mengirim `content` dengan ukuran sangat besar (MB).
- **Concurrent Write:** Dua agen menulis ke memori yang sama secara simultan.
- **Empty Tags:** Mengirim tag sebagai string kosong atau null.

---

# Database & Contract Blueprint — MCP Memory Module
**Peran:** Principal Backend Engineer  
**Status:** FINAL (Instruksi Implementasi Laravel)  
**Tujuan:** Spesifikasi teknis tingkat rendah untuk tabel, indeks, dan pemetaan kontrak API.

---

## 1. Final Database Table List

Tabel-tabel berikut adalah struktur final yang wajib diimplementasikan. Penggunaan tabel di luar daftar ini dilarang.

1. `repositories`
2. `memories`
3. `memory_versions`
4. `memory_audit_logs`
5. `organizations` (Exist/Shared)

---

## 2. Table Responsibility Definition

| Tabel               | Tanggung Jawab Bisnis                        | Jenis Data      | Source of Truth    |
| :------------------ | :------------------------------------------- | :-------------- | :----------------- |
| `repositories`      | Manajemen identitas dan isolasi proyek.      | Metadata Proyek | Ya                 |
| `memories`          | State aktif memori terkini yang bisa dibaca. | Konteks Faktual | Ya (Current State) |
| `memory_versions`   | Riwayat perubahan konten memori secara utuh. | Versi Konten    | Ya (Historical)    |
| `memory_audit_logs` | Jejak audit operasional (siapa, kapan, apa). | Operasional     | Ya                 |

---

## 3. Column Blueprint (Deskriptif)

### A. Tabel `memories`
- `id`: Unique identifier (UUID). **[Immutable]**
- `repository_id`: Reference ke repo. Opsional untuk global memori. **[Immutable]**
- `organization_id`: Reference ke organisasi induk. **[Immutable]**
- `current_content`: Konten memori yang aktif digunakan. **[Wajib]**
- `type`: Klasifikasi (business_rule, preference, log). **[Wajib]**
- `status`: Lifecycle (verified, locked, etc). **[Wajib]**
- `is_system_wide`: Flag untuk visibilitas lintas organisasi (System Scope). **[Wajib]**

### B. Tabel `memory_versions`
- `id`: UUID.
- `memory_id`: Link ke tabel memories. **[Wajib]**
- `content`: Snapshot konten pada versi tersebut. **[Wajib]**
- `version_number`: Incremental integer per memory. **[Wajib]**
- `created_at`: Timestamp pembuatan. **[Immutable]**

### C. Tabel `memory_audit_logs`
- `id`: UUID.
- `memory_id`: Link ke memories (Relasi ke objek yang diubah).
- `actor_id`: ID pengguna/agen yang melakukan aksi.
- `actor_type`: Type aktor (system/user).
- `event`: Jenis aksi (created, updated, locked, archived).
- `metadata`: JSON payload detail perubahan.

---

## 4. Index & Uniqueness Rules

1. **Unique Index:** `repositories (slug)` - Menjamin tidak ada duplikasi identifier proyek.
2. **Compound Index:** `memories (repository_id, status, type)` - Untuk optimasi query pencarian memori spesifik repo yang sudah diverifikasi.
3. **Foreign Key Index:** Semua kolom berakhiran `_id` wajib memiliki index untuk performa join.

---

## 5. Soft Delete & Archival Strategy

- **Hard Delete:** Dilarang untuk tabel `memories`, `memory_versions`, dan `audit_logs`.
- **Soft Delete:** Menggunakan kolom `deleted_at` pada `memories`. Data yang di-delete tetap tersimpan namun status berubah menjadi `archived`.
- **Reasoning:** Setiap aturan atau keputusan AI/Manusia harus bisa ditelusuri meskipun sudah tidak berlaku lagi (Auditability).

---

## 6. Versioning & Audit Strategy

- **Trigger Version:** Setiap perubahan pada `current_content` di tabel `memories` wajib menciptakan record baru di `memory_versions`.
- **Audit Event:** Setiap perubahan status (misal: `unverified` -> `verified`) hanya dicatat di `memory_audit_logs` tanpa menciptakan versi baru (kecuali konten ikut berubah).
- **Aktor:** `actor_id` harus diisi dengan ID Agen saat aksi dilakukan via MCP, atau ID User saat dilakukan via Filament.

---

## 7. MCP Contract Mapping (Final)

| MCP Action      | Database Impact                                                                                |
| :-------------- | :--------------------------------------------------------------------------------------------- |
| `memory.write`  | Insert `memories` (draft), Insert `memory_versions` (v1), Insert `audit_logs` (event:created). |
| `memory.search` | SELECT `memories` WHERE repository_id AND status IN (verified, locked).                        |
| `memory.read`   | SELECT `memories` JOIN `memory_versions` (latest).                                             |
| `memory.lock`   | UPDATE `memories (status:locked)`, Insert `audit_logs` (event:locked).                         |

---

## 8. Boundary Enforcement Points

- **Layer Database:** FK ke `repository_id` dan `organization_id`.
- **Layer Model:** Laravel Global Scopes wajib menyaring data berdasarkan konteks `repository_id` aktif.
- **Layer Service:** `MemoryWriteService` menolak update jika `status = locked`.
- **Layer API:** Request tanpa `repository_id` ditolak oleh Middleware sebelum mencapai Controller.

---

## 9. Final Implementation Checklist

- [x] Schema DB menggunakan UUID untuk seluruh Primary Key.
- [x] Tabel Audit Logs bersifat Append-Only (Tidak ada menu Edit/Delete di Filament).
- [x] Konten memori disaring agar tidak mengandung data sensitif saat dibaca Agen.
- [x] Semua rule invariant (Locked memory, Scope isolation) tertuang dalam Unit Test.

---

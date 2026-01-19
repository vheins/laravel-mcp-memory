# Laravel Migration Blueprint — MCP Memory Module
**Peran:** Senior Laravel Backend Engineer  
**Status:** FINAL (Panduan Mekanis Pembuatan Migration)  
**Tujuan:** Memberikan spesifikasi tabel, kolom, dan indeks yang sangat eksplisit untuk diimplementasikan di Laravel.

---

## 1. Final Table List

Sistem Memory terdiri dari 4 tabel utama yang saling berelasi:
1. `repositories`
2. `memories`
3. `memory_versions`
4. `memory_audit_logs`

---

## 2. Table Detail Specifications

### A. Tabel `repositories`
- **Tujuan:** Identitas unik proyek kode sebagai boundary isolasi.
- **Primary Key:** `id` (UUID).
- **Mandatory Columns:**
  - `slug` (String, Unique): Identifier human-readable.
  - `name` (String): Nama proyek.
  - `organization_id` (UUID): Reference ke organisasi pemilik.
- **Optional Columns:** `description` (Text).
- **Soft Delete:** Tidak (Gunakan flag `is_active`).

### B. Tabel `memories`
- **Tujuan:** Menyimpan status aktif terkini dari sebuah unit memori.
- **Primary Key:** `id` (UUID).
- **Scope Columns [Wajib]:**
  - `organization_id` (UUID): Boundary kepemilikan organisasi.
  - `repository_id` (UUID, Nullable): Boundary proyek (Null = Global/System Scope).
  - `scope_type` (Enum): `system`, `organization`, `repository`, `user`.
- **Classification Columns [Wajib]:**
  - `memory_type` (Enum): `business_rule`, `decision_log`, `preference`, `system_constraint`, `documentation_ref`.
  - `status` (Enum): `draft`, `verified`, `locked`, `deprecated`.
  - `created_by_type` (Enum): `human`, `ai`.
- **Content Columns:**
  - `current_content` (Text): Konten aktif saat ini.
  - `metadata` (JSON): Metadata tambahan dari agen.
- **Immutable Columns:** `id`, `repository_id`, `organization_id`.
- **Flag:** `deleted_at` (Timestamp, Soft Delete).

### C. Tabel `memory_versions`
- **Tujuan:** Menyimpan setiap perubahan konten (`current_content`) secara historis.
- **Primary Key:** `id` (UUID).
- **Identity:** 
  - `memory_id` (UUID): Parent reference ke tabel memories.
  - `version_number` (Integer): Nomor urutan versi (incremental per memory_id).
- **Content:** `content` (Text): Snapshot konten pada versi terkait.
- **Immutable:** Semua kolom pada tabel ini (Append-only).

### D. Tabel `memory_audit_logs`
- **Tujuan:** Mencatat sejarah operasi (Siapa melakukan Apa dan Kapan).
- **Primary Key:** `id` (UUID).
- **Columns:**
  - `memory_id` (UUID): Reference ke target memori.
  - `actor_id` (UUID/String): ID Aktor (User ID atau Agent ID).
  - `actor_type` (Enum): `human`, `ai_agent`.
  - `event` (String): `created`, `updated`, `verified`, `locked`, `deprecated`, `archived`.
  - `old_value` (JSON): Snapshot sebelum perubahan.
  - `new_value` (JSON): Snapshot setelah perubahan.
- **Immutable:** Seluruh tabel (TIDAK BOLEH ada update/delete).

---

## 3. Versioning & Audit Design

- **Versioning Logic:** Setiap kali `memories.current_content` diubah, sistem wajib membuat record baru di `memory_versions`. Kolom `version_number` digunakan untuk mengidentifikasi urutan sejarah.
- **Audit Logic:** Setiap perubahan status atau konten wajib dicatat di `memory_audit_logs`. Relasi audit ke memori bersifat many-to-one.

---

## 4. Indexing Strategy (Wajib)

Untuk performa lookup `Scope Resolution` yang cepat, index berikut wajib dibuat:
1. `index_memory_scope`: `(repository_id, scope_type, status)` - Digunakan saat `memory-search`.
2. `index_memory_classification`: `(memory_type, status)` - Digunakan untuk filter dashboard.
3. `index_audit_lookup`: `(memory_id, created_at)` - Untuk timeline sejarah di UI.

---

## 5. Hard Rules for Implementation

1. **No Hard Delete:** Penghapusan memori hanya diperbolehkan melalui `Soft Delete`. Record di `memory_versions` dan `audit_logs` dilarang dihapus.
2. **Versioning Constraint:** Update konten pada `memories` tanpa mencatat versi baru di `memory_versions` dianggap sebagai kegagalan sistem.
3. **Locking Guard:** Jika `status = locked`, seluruh layer aplikasi (Model/Service) wajib memblokir aksi `UPDATE`.

---

## 6. Data Volume Consideration

- **Tabel Paling Cepat Tumbuh:** `memory_audit_logs` dan `memory_versions`.
- **Mitigasi:** Gunakan data type JSON untuk metadata agar fleksibel. Lakukan indexing yang tepat pada `created_at` untuk kebutuhan pengarsipan data lama (archival policy) di masa depan.

---

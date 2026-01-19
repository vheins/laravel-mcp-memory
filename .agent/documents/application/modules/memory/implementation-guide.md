# Panduan Implementasi Teknis — MCP Memory Server
**Peran:** Senior Backend Architect (Laravel)  
**Status:** Design & Blueprint Final  
**Tujuan:** Memberikan instruksi spesifik untuk pembuatan database, service, dan integrasi bagi developer backend.

---

## 1. Database Schema Design (Deskriptif)

Developer wajib mengikuti struktur tabel berikut dengan relasi yang ketat untuk menjamin integritas data dan isolasi repository.

### A. Tabel `repositories`
- **Tujuan:** Identitas unik untuk setiap proyek kode.
- **Data:** Nama, slug unik, deskripsi, dan status aktif.
- **Relasi:** Parent dari tabel `memories`.
- **Indeks:** Index pada `slug` (Unique).

### B. Tabel `memories`
- **Tujuan:** Penyimpanan inti unit memori.
- **Data:** 
  - Konten tekstual (fakta/keputusan).
  - Metadata (JSON) untuk informasi tambahan agen.
  - Tipe memori (Enum).
  - Status siklus hidup (Enum).
- **Relasi:** 
  - `repository_id` (Foreign Key ke `repositories`, Nullable untuk global memori).
  - `organization_id` (Foreign Key, untuk corporate rules).
  - `user_id` (Foreign Key, untuk personal preference).
- **Indeks:** `repository_id`, `status`, `type`.

### C. Tabel `memory_audit_logs`
- **Tujuan:** Pencatatan sejarah perubahan yang immutable.
- **Data:** Snapshot nilai lama, nilai baru, alasan perubahan, dan aktor (AI/Manusia).
- **Constraint Logis:** Tidak boleh ada aksi `UPDATE` atau `DELETE` pada tabel ini setelah data masuk.

### D. Tabel `memory_scopes` (Optional/Meta)
- **Tujuan:** Jika diperlukan abstraksi lebih lanjut untuk permission atau tagging grup memori.

---

## 2. Memory Data Structure

Setiap record memori wajib memiliki struktur data berikut:

- **Type:** `business_rule`, `preference`, `decision_log`, `system_constraint`, `documentation_ref`.
- **Scope:** `repository`, `organization`, `user`, `system`.
- **Status:** `unverified`, `verified`, `locked`, `deprecated`.
- **Is Locked:** Boolean (Flag redundan untuk percepatan lookup, sinkron dengan status `locked`).
- **Created By:** Deskriptor aktor (e.g., `ai_agent_id` atau `user_id`).
- **Confidence Level:** Float (0.0 - 1.0), mencerminkan tingkat kepastian AI saat menulis memori.

---

## 3. Service Layer Responsibility

Implementasi wajib dipusatkan pada Service Layer untuk memastikan logika "Scope Resolution" tidak bocor ke Controller atau MCP Endpoint.

- **MemoryScopeResolver:** Bertanggung jawab melakukan query berjenjang (Repo -> User -> Org -> System) dan menggabungkan hasilnya berdasarkan prioritas.
- **MemorySearchService:** Menangani pencarian tekstual pada memori yang sudah di-resolve oleh `ScopeResolver`.
- **MemoryWriteService:** Menangani validasi penulisan. Wajib mengecek apakah AI mencoba menulis ke tipe yang dilarang atau ke memori yang sudah `Locked`.
- **MemoryLockService:** Logika spesifik untuk penguncian memori oleh Admin, termasuk validasi integritas terhadap Global Rules.

---

## 4. MCP Endpoint Mapping

Mapping antara alat (tool) MCP dan logika internal:

- **`memory.write`** -> Memanggil `MemoryWriteService@store`. Validasi: `repository_id` wajib ada, tipe tidak boleh `business_rule`.
- **`memory.search`** -> Memanggil `MemorySearchService@search`. Parameter: `query`, `repository_id`. Output: Konteks terformat untuk LLM.
- **`memory.read`** -> Mengambil detail memori tunggal. Validasi: Kepemilikan `repository_id`.
- **`memory.delete`** -> Memanggil `MemoryWriteService@destroy`. Validasi: Hanya untuk status non-locked.

---

## 5. Filament Integration Plan

- **MemoryResource:** CRUD utama untuk Admin.
- **Table Actions:** 
  - `VerifyAction`: Update status ke `verified`.
  - `LockAction`: Update status ke `locked` (dengan modal konfirmasi).
- **Global Filters:** Admin harus bisa memfilter seluruh dashboard berdasarkan satu atau beberapa Repository secara cepat.
- **View Page Integration:** Gunakan Relation Manager untuk menampilkan `MemoryAuditLog` sebagai timeline sejarah di bawah detail memori.

---

## 6. AI Agent Interaction Flow

1. **Context Initialization:** Agent mendeteksi `repository_id` aktif dari lingkungan kerja.
2. **Knowledge Retrieval:** Agent memanggil `memory.search` dengan kueri masalah yang sedang dihadapi.
3. **Internal Resolution:** 
   - Sistem mengambil memori spesifik repo tersebut.
   - Sistem mengambil aturan global organisasi.
   - Sistem menggabungkan dan menyaring berdasarkan status `verified` vs `unverified`.
4. **Context Injection:** Sistem mengembalikan daftar memori yang telah diformat sebagai instruksi tambahan untuk LLM.
5. **Response Generation:** Agent memberikan jawaban yang akurat berdasarkan instruksi memori tersebut.

---

## 7. Error Handling Strategy

- **Memory Conflict:** Jika terdapat memori `Verified` yang kontradiktif dengan `Unverified`, sistem wajib memenangkan yang `Verified`.
- **Scope Mismatch:** Jika agen mencoba mengakses `repository_id` yang tidak sesuai dengan hak akses tokennya, sistem mengembalikan `403 Forbidden`.
- **Unauthorized Write:** Jika agen mencoba menulis tipe yang diproteksi, sistem mengembalikan error message yang menjelaskan batasan tersebut.

---

## 8. Phase-Based Implementation Plan

- **Fase 1: Dasar & CRUD:** Migrasi tabel utama, Model Laravel, dan UI Filament dasar untuk input manual manusia.
- **Fase 2: Integrasi MCP:** Implementasi JSON-RPC endpoint dan `MemoryWriteService` dengan proteksi scope dasar.
- **Fase 3: Scope Resolution Engine:** Implementasi logika `MemoryScopeResolver` yang kompleks (penggabungan hierarki memori).
- **Fase 4: Audit & Hardening:** Aktivasi log audit otomatis dan penguncian fungsionalitas `Locked`.

---

## 9. Final Guardrails

1. **Bukan Chat History:** Developer dilarang menyimpan riwayat percakapan mentah di tabel `memories`. Hanya fakta dan keputusan yang diekstrak.
2. **Human Control:** Status `Verified` dan `Locked` adalah hak prerogatif manusia melalui Dashboard.
3. **Data Safety:** Pastikan Laravel Global Scope selalu aktif untuk `repository_id` pada Model `Memory` guna mencegah kebocoran data antar proyek.

---

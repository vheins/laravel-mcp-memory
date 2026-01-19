# Executable Architecture Specification — MCP Memory Module
**Peran:** Chief Software Architect  
**Status:** FINAL (Jembatan Arsitektur ke Kode)  
**Tujuan:** Meniadakan ambiguitas teknis pada tingkat atribut, relasi, dan invarian sistem.

---

## 1. Final Entity List & Attributes

Daftar entitas ini wajib diimplementasikan sebagai Eloquent Model (Laravel) dengan atribut yang ditentukan.

### A. Entitas `Repository`
- **Peran:** Boundary isolasi dan kepemilikan data.
- **Mutable:** Ya (untuk metadata manajemen).
- **Atribut:**
  - `id`: Unique identifier (UUID).
  - `slug`: Human-readable identifier (Unique).
  - `name`: Nama tampilan.
  - `organization_id`: Reference ke pemilik organisasi.

### B. Entitas `Memory`
- **Peran:** Penyimpanan unit informasi faktual.
- **Mutable:** Ya (dengan batasan status).
- **Atribut:**
  - `id`: UUID.
  - `content`: Teks informasi utama.
  - `type`: Enum (`business_rule`, `decision_log`, `preference`, `system_constraint`).
  - `status`: Enum (`draft`, `verified`, `locked`, `deprecated`).
  - `repository_id`: Scope repository (Nullable).
  - `is_system_wide`: Boolean flag untuk global visibility.

### C. Entitas `MemoryAudit`
- **Peran:** Immutable history tracking.
- **Mutable:** TIDAK (Append-only).
- **Atribut:**
  - `memory_id`: Link ke memori.
  - `actor_id`: ID pengguna atau agen.
  - `action`: Jenis perubahan (`create`, `update`, `lock`).
  - `payload_diff`: Perubahan nilai (JSON).

---

## 2. Relationship Map

- **Ownership:** `Organization` -> memiliki -> `Repository` -> memiliki -> `Memory`.
- **Dependency:** `MemoryAudit` sepenuhnya bergantung pada eksistensi `Memory`.
- **Isolation Boundary:** Query pada `Memory` wajib melalui filter `repository_id` kecuali memori bertipe `system_constraint`.

---

## 3. Invariant Rules (Kebenaran Mutlak)

1. **Locked Guard:** Record memori dengan `status = locked` tidak boleh menerima aksi `update` dari API manapun.
2. **Strict Isolation:** Data dengan `repository_id = A` tidak boleh muncul dalam hasil pencarian jika request meminta konteks `repository_id = B`.
3. **Hierarchy Integrity:** Aturan `System` tidak bisa ditimpa oleh `Repository` jika aturan tersebut ditandai sebagai `Immutable`.

---

## 4. Command Responsibility Map

### Command: `WriteMemory`
- **Input:** `content`, `type`, `repository_id`.
- **Pre-condition:** Repo valid, tipe bukan `business_rule`.
- **Post-condition:** Record baru dibuat dengan status `draft`.
- **Failure:** Jika repo mismatch atau tipe terlarang.

### Command: `ReadMemory`
- **Input:** `memory_id`.
- **Pre-condition:** Pengecekan akses via `repository_id`.
- **Post-condition:** Mengembalikan full record.

### Command: `SearchMemory`
- **Input:** `query_string`, `repository_id`.
- **Pre-condition:** Konteks repo tersedia.
- **Post-condition:** Mengembalikan koleksi memori (Top-10) berdasarkan hierarki scope.

---

## 5. Read Model vs Write Model

- **Read Model (Agent View):** Hanya berisi `content`, `type`, dan `origin`. Tidak menyertakan detail teknis ID database atau metadata sensitif.
- **Write Model (System View):** Berisi seluruh atribut fungsional termasuk `audit_logs`, `versions`, dan `reliability_score` untuk audit internal.

---

## 6. Memory Injection Contract

Hasil dari `SearchMemory` akan dirangkum secara naratif sebelum dikirim ke Agen:
- **Format:** `[ORIGIN: Repository] Content: ...`
- **Penyederhanaan:** Teks yang sangat panjang wajib di-truncate pada tingkat aplikasi sebelum injeksi.
- **Limit:** Maksimal 4000 karakter total per injeksi.

---

## 7. Consistency & Observability

- **Consistency:** Mengutamakan **Strong Consistency** untuk memori berstatus `Locked` guna menjamin agen selalu mendapatkan aturan terbaru.
- **Observability:** Sistem wajib mencatat `agent_id` pada setiap aksi `Read` atau `Write` untuk menjawab pertanyaan audit: "Kapan agen ini menggunakan informasi ini?".

---

## 8. Implementation Checklist (Final Step)

- [ ] Skema database mendukung UUID sebagai PK.
- [ ] Middleware akses Repository telah diuji.
- [ ] Logika `Switch Case` untuk prioritas scope sudah didefinisikan secara eksplisit di service layer.
- [ ] Tidak ada fungsi "Delete" fisik, hanya status `deprecated` atau `archived`.

---

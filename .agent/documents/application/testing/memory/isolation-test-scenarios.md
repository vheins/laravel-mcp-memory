# MCP Memory Isolation Test Scenarios
**Peran:** QA Architect for AI Infrastructure  
**Status:** MANDATORY (Kontrak Verifikasi)  
**Tujuan:** Menjamin isolasi repository, integritas data, dan kepatuhan kebijakan AI.

---

## 1. Repository & Organization Isolation

### Skenario: Cross-Repository Read Violation
- **Given:** Memori faktual ("Aturan Pajak Proyek A") tersimpan eksklusif di `repository_id: REPO-A`.
- **When:** AI Agent melakukan `memory.search` dengan parameter `repository_id: REPO-B`.
- **Then:** Sistem wajib mengembalikan hasil kosong (Empty Array) dan tidak membocorkan data dari REPO-A.

### Skenario: Cross-Organization Isolation
- **Given:** Memori level organisasi ("Kebijakan Vendor Org-X") tersimpan di `organization_id: ORG-X`.
- **When:** Request dilakukan dengan `organization_id: ORG-Y`.
- **Then:** Sistem harus menolak akses dan tidak mengembalikan memori milik ORG-X.

---

## 2. Memory Protection & Write Restriction

### Skenario: Unauthorized Write by AI (Hallucination Guard)
- **Given:** AI Agent mencoba menulis unit memori.
- **When:** Payload `type` diatur sebagai `business_rule` atau `system_constraint`.
- **Then:** Sistem menolak request (Reject), memberikan error `WRITE_NOT_ALLOWED`, dan mencatat upaya pelanggaran pada Security Log.

### Skenario: Locked Memory Protection
- **Given:** Sebuah unit memori memiliki status `locked`.
- **When:** AI Agent mengirim `memory.write` (update) atau `memory.delete` pada ID memori tersebut.
- **Then:** Sistem mengembalikan error `MEMORY_LOCKED` dan tidak melakukan modifikasi apapun pada database.

---

## 3. Human Write & Governance Flow

### Skenario: Human Rule Creation (Authority Check)
- **Given:** Admin Filament dengan hak akses Architect.
- **When:** Menulis memori tipe `business_rule` dan mengubah status menjadi `verified`.
- **Then:** Sistem menerima request, menyimpan data secara permanen, dan memicu audit trail yang mencatat nama Admin sebagai aktor.

---

## 4. Hierarchy & Context Enforcement

### Skenario: Scope Priority Resolution
- **Given:** Terdapat konflik nilai antara `System Constraint` (Global) dan `Repository Preference` (Local).
- **When:** AI Agent melakukan `memory.search`.
- **Then:** Sistem mengembalikan memori `System Constraint` sebagai prioritas yang tidak dapat diganggu gugat jika ditandai sebagai `Enforced`.

### Skenario: Missing Context Enforcement
- **Given:** Request ke MCP endpoint dikirimkan.
- **When:** Field `repository_id` atau `agent_id` kosong/null.
- **Then:** Sistem mengembalikan error `INVALID_CONTEXT` dan menghentikan proses sebelum query ke database dilakukan.

---

## 5. Search Accuracy & Filtering

### Skenario: Filter Inactive Memory
- **Given:** Terdapat memori dengan status `deprecated` dan `archived`.
- **When:** AI Agent melakukan `memory.search`.
- **Then:** Sistem secara otomatis memfilter (menyembunyikan) memori tersebut dari hasil pencarian agar agen tidak mendapatkan informasi usang.

---

## 6. Audit & Versioning Verifier

### Skenario: Versioning on Update
- **Given:** Memori aktif ("Versi 1") diperbarui oleh Admin.
- **When:** Perubahan disimpan.
- **Then:** Tabel `memory_versions` bertambah satu record baru, dan kolom `current_content` pada tabel utama terupdate sesuai nilai terbaru.

### Skenario: Audit Trail Evidence
- **Given:** Setiap aksi modifikasi memori dilakukan (write/lock/deprecate).
- **When:** Aksi berhasil.
- **Then:** Tabel `memory_audit_logs` harus memiliki entri yang mencantumkan `actor_id`, `timestamp`, dan `request_id` yang sesuai.

---

## 7. Reliability & Fallback

### Skenario: Agent Resilience on Service Failure
- **Given:** Service Memory Backend mengalami kendala (Down).
- **When:** AI Agent memanggil tool memory.
- **Then:** Agent tidak boleh berhenti/crash, melainkan harus memberikan fallback response kepada user yang menyatakan keterbatasan konteks memori saat ini.

---

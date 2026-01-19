# Technical Blueprint — MCP Memory Module
**Peran:** Lead System Architect  
**Status:** High-Level Design Approved  
**Tujuan:** Panduan teknis final untuk implementasi Backend, Frontend (Filament), dan MCP Interface.

---

## 1. Domain Model Breakdown

Struktur data harus mencerminkan isolasi scope dan manajemen siklus hidup memori.

### A. Memory
- **Tanggung Jawab:** Menyimpan unit informasi (fakta/keputusan/preferensi).
- **Kepemilikan Data:** Dimiliki secara hierarki oleh Repository, User, atau Organization.
- **Relasi:** 
  - BelongsTo `Repository` (Nullable)
  - BelongsTo `User` (Nullable)
  - BelongsTo `Organization` (Nullable)
  - HasMany `AuditLog`.

### B. Repository
- **Tanggung Jawab:** Boundary utama isolasi data teknis.
- **Kepemilikan Data:** Dimiliki oleh `Organization`.
- **Relasi:** HasMany `Memory`.

### C. Organization
- **Tanggung Jawab:** Boundary kebijakan perusahaan (Global Rules).
- **Relasi:** HasMany `Repository`, HasMany `User`, HasMany `Memory`.

### D. MemoryAuditLog
- **Tanggung Jawab:** Immutable record untuk setiap perubahan state atau content.
- **Relasi:** BelongsTo `Memory`. Mencatat `actor_id` dan `actor_type` (AI vs Human).

---

## 2. Memory Scope Resolution Flow (Naratif)

Saat AI Agent melakukan pencarian konteks (`memory-search`), sistem harus meresolusi memori berdasarkan urutan prioritas berikut:

1. **Langkah 1 (Targeted Scope):** Sistem mencari memori yang terikat langsung pada `repository_id` yang sedang aktif. Ini adalah informasi paling spesifik (contoh: spesifikasi teknis repo tersebut).
2. **Langkah 2 (User Preference):** Mencari memori yang terikat pada `user_id` yang memicu aksi. Ini memberikan konteks gaya kerja atau preferensi personal user.
3. **Langkah 3 (Corporate Policy):** Mencari memori di level `organization_id`. Ini mencakup aturan umum perusahaan yang berlaku di semua repo.
4. **Langkah 4 (System Baseline):** Terakhir, sistem mengambil memori level `System` yang merupakan batasan fundamental platform.

**Hasil Akhir:** Kumpulan memori ini digabungkan (Union) dengan filter duplikasi: informasi di level lebih tinggi (Repo) dapat menimpa informasi level rendah (Org), kecuali jika Level rendah ditandai sebagai `Enforced`.

---

## 3. Memory Priority Matrix

| Atribut                         | Aturan Conflict Resolution                                                                                |
| :------------------------------ | :-------------------------------------------------------------------------------------------------------- |
| **Business Rule vs Preference** | `business_rule` memenangkan konflik logika atas `preference`.                                             |
| **Locked vs Editable**          | Memori `Locked` bersifat absolut dan mengabaikan instruksi perubahan dari AI.                             |
| **System vs Repository**        | `Repository` menang untuk detail teknis, `System` menang untuk batasan keamanan/arsitektur.               |
| **Human vs AI Generated**       | Input Manusia (`Verified`) selalu menang atas hipotesis AI (`Unverified`) jika terjadi kontradiksi fakta. |

---

## 4. Write Permission Rules

Keamanan penulisan memori adalah kunci integritas sistem:

- **Boleh ditulis AI Agent:**
  - Tipe: `decision_log`, `preference`, `documentation_ref`.
  - Level: Hanya `Repository Scope`.
  - State: Selalu dimulai sebagai `draft` atau `unverified`.
- **Hanya boleh ditulis Manusia (Dashboard):**
  - Tipe: `business_rule`, `system_constraint`.
  - Level: `Organization Scope` & `System Scope`.
  - Action: Mengaktifkan state `verified` atau `locked`.
- **Tidak boleh diubah sama sekali (Immutable):**
  - Memori dengan status `locked`.
  - Riwayat audit (`MemoryAuditLog`).

---

## 5. Memory Lifecycle State

1. **Draft / Unverified:** Status awal memori yang ditulis oleh AI. Belum bisa dianggap sebagai "Kebenaran Mutlak".
2. **Active / Verified:** Memori yang sudah direview oleh manusia. Siap digunakan sebagai referensi utama.
3. **Locked:** Status tertinggi. Memori menjadi "Golden Rule" yang tidak bisa dihapus atau diubah oleh AI.
4. **Deprecated:** Memori yang masih ada namun ditandai sebagai "usang". Admin harus menyediakan alasan dan referensi memori pengganti.
5. **Archived (Soft Delete):** Memori yang tidak lagi aktif dalam pencarian rutin namun tetap ada untuk kebutuhan audit sejarah.

---

## 6. Filament Dashboard Mapping

| Fitur Filament          | Deskripsi Teknis                                                                          |
| :---------------------- | :---------------------------------------------------------------------------------------- |
| **Memory Management**   | Resource utama untuk CRUD memori oleh Admin.                                              |
| **Scope Filter**        | Sidebar filter untuk membatasi view berdasarkan Repository atau Organization.             |
| **Verification Action** | Tombol cepat untuk mengubah status `Unverified` -> `Verified`.                            |
| **Lock Flow**           | Dialog konfirmasi untuk mengunci memori, termasuk pengecekan konflik dengan Global Rules. |
| **Audit Timeline**      | Tab khusus di halaman View untuk melihat riwayat perubahan (Spatie Activity Log).         |

---

## 7. API Responsibility Boundary

- **MCP Endpoint (Interface):**
  - Hanya menerima request JSON-RPC.
  - Melakukan validasi payload dasar.
  - Mendelegasikan logika bisnis ke Service Layer.
- **Internal Service Layer (Core):**
  - Mesin dibalik Scope Resolution.
  - Melakukan pengecekan `Locked` status sebelum modifikasi.
  - Memastikan `repository_id` sesuai dengan konteks token.
- **Filament Admin Action (UI Integration):**
  - Satu-satunya titik masuk untuk prosedur `Locking`.
  - Menangani interaksi manusia-ke-data yang bersifat administratif.

---

## 8. Non-Functional Requirements

- **Auditability:** Setiap baris memori harus memiliki `trace_id` yang menghubungkan ke percakapan AI asal.
- **Traceability:** Developer harus bisa menjawab "Siapa/Apa yang membuat aturan ini?" lewat Audit Log.
- **Data Isolation:** Menggunakan Laravel Global Scopes untuk memastikan satu tenant tidak pernah melihat memori tenant lain secara tidak sengaja.
- **Future Vector Support:** Skema tabel harus menyediakan kolom `embedding` (vector type) untuk kebutuhan integrasi Vector DB di masa depan.
- **Performance:** Pencarian memori harus dibatasi (e.g., top 5-10) untuk menghindari *token bloat* pada LLM context.

---

## 9. Explicit Exclusions (TIDAK BOLEH)

- **Auto-Learning:** Sistem dilarang mengubah status memori menjadi `Verified` secara otomatis tanpa campur tangan manusia.
- **Auto-Promotion:** AI tidak boleh mempromosikan memori dari Repo Scope ke Organization Scope sendiri.
- **Inference-based Write:** AI dilarang menulis memori berdasarkan "asumsi" tanpa adanya instruksi atau keputusan eksplisit dalam sesi.
- **Implicit Rule Creation:** Semua aturan bisnis (`business_rule`) harus didefinisikan secara eksplisit oleh manusia.

---

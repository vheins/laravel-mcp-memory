# Service Layer Blueprint — MCP Memory Module
**Peran:** Lead Laravel Architect  
**Status:** FINAL (Panduan Implementasi Logic)  
**Tujuan:** Memastikan pemisahan logika bisnis dari infrastruktur (Filament/MCP) dan menjamin integritas aturan memori.

---

## 1. Service List (FINAL)

Seluruh logika bisnis wajib diletakkan dalam service berikut. Akses langsung ke Eloquent Model dari Controller atau Filament Resource sangat dilarang.

1. **MemoryWriteService:** Menangani pembuatan dan pembaruan unit memori.
2. **MemoryReadService:** Menangani pengambilan detail memori tunggal dengan validasi akses.
3. **MemorySearchService:** Menangani pencarian memori (RAG) berbasis kueri.
4. **MemoryLockService:** Menangani prosedur penguncian (locking) dan pembukaan kunci (unlocking).
5. **MemoryScopeResolver:** Komponen inti untuk resolusi hierarki memori (Repo -> User -> Org -> System).
6. **MemoryAuditService:** Menangani pencatatan sejarah versi dan log audit secara otomatis.

---

## 2. Responsibility per Service

### MemoryWriteService
- **Boleh:** Melakukan validasi tipe memori berdasarkan aktor (AI vs Human), memicu pembuatan versi baru, memanggil audit service.
- **Tidak Boleh:** Melakukan penguncian memori (delegasikan ke `MemoryLockService`).

### MemorySearchService
- **Boleh:** Memanggil `MemoryScopeResolver` untuk mendapatkan list memori yang valid sesuai konteks.
- **Tidak Boleh:** Mengubah state memori (Read-Only).

### MemoryScopeResolver
- **Boleh:** Mengelola logika prioritas antar scope dan melakukan deduplikasi hasil pencarian.
- **Tidak Boleh:** Mengenal interface eksternal (MCP/Web).

---

## 3. Input Contract

Setiap service wajib menerima struktur input yang mencakup objek konteks minimal:
- **`IdentityContext`:** Berisi `repository_id`, `organization_id`, dan `agent_id` (jika dari agen).
- **`Payload`:** Data fungsional yang akan diproses.
- **Validasi Awal:** Service wajib melakukan pengecekan keberadaan ID yang dikirimkan sebelum eksekusi logic.

---

## 4. Business Rule Enforcement

- **Scope Isolation:** Service wajib memastikan data tidak bocor antar `repository_id` melalui penerapan global scope otomatis.
- **Locked Protection:** `MemoryWriteService` wajib menolak aksi `update` jika status memori target adalah `locked`.
- **Versioning Enforcement:** Setiap aksi `write` yang merubah konten wajib melalui `MemoryAuditService` untuk diarsipkan versinya.

---

## 5. Service Interaction Flow

1. **Entry Point:** Controller (MCP) atau Action (Filament) menerima request.
2. **Context Setup:** Membangun `IdentityContext`.
3. **Primary Service:** Memanggil service utama (misal: `MemoryWriteService`).
4. **Internal Dependency:** Service utama memanggil `MemoryAuditService` untuk pencatatan sejarah.
5. **Persistence:** Service melakukan operasi ke Model/Repository.

---

## 6. Forbidden Access Patterns

- **Anti-Pattern 1:** Menggunakan `Memory::create()` atau `Memory::update()` langsung di Controller.
- **Anti-Pattern 2:** Menulis logika `if (locked)` di dalam Filament Resource. Logika ini harus ada di `MemoryWriteService`.
- **Anti-Pattern 3:** Melakukan query pindah-scope (cross-scope) tanpa melalui `MemoryScopeResolver`.

---

## 7. Error & Transaction Boundary

- **Error Responsibility:** Exception dilempar oleh Service saat terjadi pelanggaran aturan bisnis (misal: `LockedMemoryException`). Controller bertanggung jawab menerjemahkan exception tersebut menjadi HTTP/JSON-RPC error code.
- **Transaction Boundary:**
  - `MemoryWriteService` dan `MemoryLockService` **WAJIB** berada dalam Database Transaction untuk menjamin sinkronisasi antara tabel Memori, Versi, dan Audit.
  - `MemoryReadService` dan `MemorySearchService` bersifat **Read-Only** (Non-Transactional).

---

## 8. Extensibility: Vector Memory Support

Untuk penambahan dukungan Vector DB di masa depan:
- `MemorySearchService` akan bertindak sebagai orchestrator yang memanggil `SemanticSearchProvider`.
- Perubahan ini tidak boleh merusak `MemoryScopeResolver` karena pemfilteran scope tetap dilakukan di level database relasional sebelum/sesudah pencarian vektor.

---

## 9. Implementation Checklist

- [ ] Seluruh service terdaftar sebagai Singleton di Service Provider.
- [ ] Interface didefinisikan untuk service yang memungkinkan implementasi alternatif (misal: Search).
- [ ] Unit Test dibuat per service dengan mocking pada dependency-nya.

---

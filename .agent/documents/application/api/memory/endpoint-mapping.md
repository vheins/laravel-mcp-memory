# MCP Endpoint Mapping Blueprint — MCP Memory Module
**Peran:** MCP Integration Architect  
**Status:** FINAL (Panduan Implementasi Controller)  
**Tujuan:** Memetakan protokol MCP ke dalam struktur Controller Laravel dan memastikan integrasi yang bersih dengan Service Layer.

---

## 1. MCP Tool → Controller Mapping

Setiap tool MCP wajib dipetakan ke satu Dedicated Controller untuk menjaga prinsip Single Responsibility.

| MCP Tool           | Target Controller           | Service Utama         |
| :----------------- | :-------------------------- | :-------------------- |
| `memory-write`     | `MemoryWriteController`     | `MemoryWriteService`  |
| `memory.read`      | `MemoryReadController`      | `MemoryReadService`   |
| `memory-search`    | `MemorySearchController`    | `MemorySearchService` |
| `memory.lock`      | `MemoryLockController`      | `MemoryLockService`   |
| `memory.deprecate` | `MemoryDeprecateController` | `MemoryWriteService`  |

---

## 2. Controller Responsibility

Controller dalam arsitektur ini bersifat **"Thin"** dan hanya bertanggung jawab atas:
- **Transport Translation:** Mengubah JSON-RPC payload menjadi objek internal.
- **Context Hydration:** Mengambil data identitas agen dari header/token.
- **Request Validation:** Memastikan skema data request valid secara struktural.
- **Response Formatting:** Membungkus hasil dari service ke dalam format MCP standard.

**Dilarang:** Menulis logika bisnis, query database, atau penanganan resolusi scope di dalam Controller.

---

## 3. Request Validation Boundary

Controller wajib menegakkan validasi tingkat entri berikut sebelum memanggil Service:

1. **Context Validation:** 
   - `repository_id` harus berupa UUID valid.
   - `agent_id` tidak boleh kosong.
   - `organization_id` wajib ada dalam struktur context.
2. **Intent Validation:** Memastikan `intent` pada request sesuai dengan fungsionalitas tool (misal: `memory-search` harus memiliki intent `read`).
3. **Type Validation:** Memastikan `memory_type` berada dalam list Enum yang diizinkan.

---

## 4. Controller Flow (Standard Procedure)

Step-by-step eksekusi di level Controller:
1. **Receive:** Menerima payload JSON-RPC melalui HTTP POST.
2. **Validate:** Menjalankan Laravel FormRequest Validation.
3. **Hydrate:** Membangun objek `IdentityContext`.
4. **Invoke:** Memanggil method yang relevan pada Service.
5. **Translate:** Jika service melempar Exception, tangkap dan terjemahkan ke Error Code MCP.
6. **Return:** Mengirimkan JSON response sesuai kontrak.

---

## 5. Error Translation Layer

Controller bertindak sebagai mapper antara Domain Exception dan Protocol Error:

| Domain Exception              | MCP Error Code               | Makna                                      |
| :---------------------------- | :--------------------------- | :----------------------------------------- |
| `ValidationException`         | `INVALID_CONTEXT` (-32602)   | Parameter tidak lengkap atau format salah. |
| `ScopeViolationException`     | `SCOPE_VIOLATION` (-32002)   | Mencoba akses repo di luar otoritas.       |
| `LockedMemoryException`       | `MEMORY_LOCKED` (-32001)     | Mencoba modifikasi data yang di-lock.      |
| `UnauthorizedAccessException` | `WRITE_NOT_ALLOWED` (-32003) | AI mencoba menulis tipe terproteksi.       |
| `ModelNotFoundException`      | `MEMORY_NOT_FOUND` (-32004)  | ID memori tidak ditemukan.                 |

---

## 6. Stateless & Security Gate

- **Stateless:** Controller dilarang menggunakan session Laravel (`Session::put`, dll) atau menyimpan state lokal. Setiap request harus independen.
- **Auth Middleware:** Seluruh rute MCP wajib dilindungi oleh `McpAuthMiddleware` yang memverifikasi token dan meng-inject `organization_id`.
- **Scope Middleware:** Menggunakan middleware untuk memastikan `repository_id` pada request benar-benar dimiliki oleh organisasi tersebut.

---

## 7. Logging & Traceability

- **Correlation ID:** Controller wajib menangkap `request_id` dari MCP payload dan menyertakannya dalam setiap log Laravel agar dapat ditelusuri.
- **Sensitive Data:** Dilarang me-log konten memori (current_content) dalam log aplikasi untuk menjaga privasi data. Cukup log metadata (ID, Type, Scope).

---

## 8. Response Contract (Conceptual)

- **Success:** Selalu mengembalikan objek `result` sesuai spec MCP.
- **Error:** Selalu mengembalikan objek `error` dengan `code`, `message`, dan `data` (opsional untuk detail validasi).

---

## 9. Forbidden Patterns

- **TIDAK BOLEH** mengakses `DB::table()` atau `Memory::query()` langsung.
- **TIDAK BOLEH** menentukan prioritas scope di Controller.
- **TIDAK BOLEH** melakukan bypass validasi aktor (AI vs Human).

---

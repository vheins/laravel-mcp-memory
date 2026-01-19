# MCP Memory Tool Specification
**Peran:** MCP Protocol Architect  
**Status:** FINAL (Kontrak Teknis Antarmuka)  
**Tujuan:** Definisi teknis tool MCP untuk implementasi client-server tanpa ambiguitas.

---

## 1. Common Request Context

Setiap request ke MCP Memory Server **WAJIB** menyertakan objek `context` berikut dalam parameter:

- **`agent_id`**: ID unik dari AI Agent yang melakukan request.
- **`organization_id`**: UUID organisasi konteks.
- **`repository_id`**: UUID repository aktif.
- **`intent`**: Klasifikasi aksi (`read` | `write` | `admin`).
- **`request_id`**: Identifikasi unik request untuk pelacakan audit.

---

## 2. Tool Specification

### A. `memory-write`
- **Purpose**: Menyimpan fakta atau log keputusan baru.
- **Allowed Caller**: AI Agent, Human Admin.
- **Required Fields**: `content`, `type`.
- **Validation**: `type` dilarang bernilai `business_rule` atau `system_constraint` jika dikirim oleh AI.
- **Forbidden Scenario**: Mengedit memori yang berstatus `locked`.
- **Response**: Mengembalikan `memory_id` dan status `unverified`.
- **Audit**: Selalu memicu `created` audit record dan `v1` memory version.

### B. `memory-search`
- **Purpose**: Pencarian memori relevan (RAG).
- **Allowed Caller**: AI Agent, Human Admin.
- **Required Fields**: `query`.
- **Optional Fields**: `limit` (default: 5, max: 10), `type_filter`.
- **Validation**: Wajib membawa `repository_id` valid.
- **Response**: Daftar objek memori (content, status, type, scope_origin).
- **Audit**: Dicatat sebagai aksi `read` pada audit log untuk kebutuhan observabilitas.

### C. `memory.read`
- **Purpose**: Mengambil detail lengkap memori tunggal.
- **Required Fields**: `memory_id`.
- **Response**: Full memory object termasuk riwayat versi terakhir.

### D. `memory.lock`
- **Purpose**: Mengunci memori agar menjadi immutable bagi AI.
- **Allowed Caller**: Human Admin (Architect/Manager role).
- **Forbidden Scenario**: AI Agent memanggil tool ini.
- **Response**: Konfirmasi status `locked` dan timestamp.

### E. `memory.deprecate`
- **Purpose**: Menandai memori sebagai usang/tidak berlaku.
- **Required Fields**: `memory_id`, `reason`.
- **Response**: Status berubah menjadi `deprecated`.

---

## 3. Scope & Type Enforcement

- **Scope Validation**: Server wajib memvalidasi bahwa `repository_id` pada request sesuai dengan hak akses yang terikat pada token/session.
- **No Cross-Repo**: Request dengan `repository_id` A dilarang membaca data milik `repository_id` B.
- **System Scope**: Hanya dapat diakses melalui operasi `read` atau `search`. Tidak ada penulisan level system via MCP umum.

---

## 4. Result Limitation & Trimming

- **Maximum Results**: `memory-search` dibatasi maksimal **10 record**.
- **Ordering Priority**: 
  1. Exact Match pada Repository Scope.
  2. Semantic Match pada Repository Scope.
  3. Verified Organization Rules.
- **Trimming Strategy**: Metadata yang tidak relevan bagi AI (ID database internal, internal timestamps) akan dibuang sebelum dikirim.

---

## 5. Error Code Contract

| Error Code          | Detail Bisnis                                                       |
| :------------------ | :------------------------------------------------------------------ |
| `INVALID_CONTEXT`   | Request tidak menyertakan context minimal (repo_id/agent_id).       |
| `SCOPE_VIOLATION`   | Mencoba mengakses data di luar repository aktif.                    |
| `WRITE_NOT_ALLOWED` | AI mencoba menulis tipe memori terproteksi (business_rule).         |
| `MEMORY_LOCKED`     | Mencoba memodifikasi record berstatus locked.                       |
| `MEMORY_NOT_FOUND`  | Identifier memori tidak ditemukan atau tidak tersedia di scope ini. |

---

## 6. Security & Audit Notes

- **Explicit Write**: Semua penulisan harus berupa perintah eksplisit. Server tidak diperbolehkan melakukan "auto-learn" dari percakapan tanpa tool call.
- **No Inference**: Server dilarang melakukan inferensi atau modifikasi konten memori secara otomatis.
- **Transparency**: Setiap aksi MCP selalu meninggalkan jejak audit yang menghubungkan `agent_id` dengan `memory_id`.

---

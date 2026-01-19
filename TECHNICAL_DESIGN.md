# TECHNICAL DESIGN DOCUMENT: MCP Memory Server (Laravel + Filament)

**Status:** Draft / Approved
**Version:** 1.0.0
**Role:** Technical Documentation Architect

---

## 1. Overview System

### Apa itu MCP Memory Server?
MCP (Model Context Protocol) Memory Server adalah layanan backend terpusat yang berfungsi sebagai "eksternal brain" (memori eksternal) untuk AI Agent. Sistem ini menyediakan mekanisme penyimpanan (persistence), pengambilan (retrieval), dan manajemen state yang persisten dan terstruktur. Berbeda dengan *context window* bawaan LLM yang bersifat sementara (ephemeral), MCP Memory Server menjamin data tetap ada antar sesi percakapan.

### Peran dalam Arsitektur AI Agent
Dalam ekosistem AI Agent (khususnya untuk ERP dan Procurement), sistem ini berperan sebagai:
1.  **Source of Truth:** Menyimpan aturan bisnis dan log keputusan yang tidak boleh dilanggar oleh AI.
2.  **Context Provider:** Menyediakan informasi relevan secara dinamis ke dalam *prompt context* AI sebelum AI memproses permintaan user.
3.  **Audit Logger:** Mencatat setiap perubahan state atau memori yang dilakukan oleh AI maupun manusia.

### Mengapa Laravel + Filament?
*   **Laravel:** Framework PHP yang matang dengan *Eloquent ORM* yang kuat untuk menangani relasi data yang kompleks (structured memory). Stabilitas dan keamanannya sangat cocok untuk lingkungan *enterprise* on-premise.
*   **Filament:** Admin panel berbasis TALL stack yang terintegrasi erat dengan Laravel. Filament akan digunakan sebagai antarmuka "Human-in-the-loop" untuk memvalidasi, mengedit, dan memantau memori yang dibuat atau dibaca oleh AI. Ini penting untuk mencegah "black box" behavior pada AI.

---

## 2. High Level Architecture

Sistem ini beroperasi sebagai jembatan antara AI Agent dan penyimpanan data persisten.

### Komponen Utama
1.  **AI Agent (Client):** Sistem yang menjalankan LLM (Large Language Model) yang membutuhkan memori jangka panjang.
2.  **MCP Interface Layer:** Titik masuk (API) yang mengimplementasikan standar Model Context Protocol untuk komunikasi standar.
3.  **Memory Logic (Laravel):** Core business logic yang menangani klasifikasi, validasi, dan penyimpanan memori.
4.  **Database Storage:** Database relasional (PostgreSQL/MySQL) untuk menyimpan structured memory.
5.  **Filament Dashboard:** UI untuk Administrator/Manager memantau dan mengelola memori.

### Arsitektur Alur Data (Text Diagram)

```text
[ HUMAN USER ]
      |
      v
[ FILAMENT DASHBOARD ] <-----> [ LARAVEL APP (Core Logic) ] <=====> [ DATABASE STORAGE ]
      ^                                   ^                                  ^
      | (Verifikasi/Edit)                 | (CRUD Logic)                     | (Persistance)
      |                                   v                                  |
      |                        [ MCP INTERFACE LAYER ]                       |
      |                                   ^                                  |
      |                                   | (JSON-RPC)                       |
      |                                   v                                  |
      --------------------------- [ AI AGENT ] <------------------------------
                                  (Client MCP)
```

**Keterangan Hubungan:**
*   **AI Agent** mengirim request (Read/Write) ke **MCP Interface**.
*   **Laravel App** memproses request, memvalidasi aturan, dan menyimpan/mengambil data dari **Database**.
*   **Filament Dashboard** mengakses Database yang sama melalui Laravel Model, memungkinkan manusia mengintervensi memori yang digunakan AI.

---

## 3. Memory Concept

Konsep dasar dari sistem ini adalah **Retrieval-Augmented Generation (RAG)** sederhana namun terstruktur.

### Jenis Memori
1.  **Short-term Memory:**
    *   Konteks percakapan saat ini. Biasanya ditangani oleh *client* (Agent), namun MCP Server dapat menyimpan *session summary* jika diperlukan untuk sesi berlanjut.
2.  **Long-term Memory:**
    *   Pengetahuan yang disimpan secara permanen. Contoh: Sejarah pembelian vendor tahun lalu, preferensi user.
3.  **Structured Memory:**
    *   Data yang memiliki skema ketat (Row & Column). Sangat krusial untuk data ERP. Contoh: Tabel `vendor_limits`, `approval_hierarchy`.
4.  **Semantic / Vector Memory (Future Scope):**
    *   Penyimpanan berbasis embedding untuk pencarian kesamaan makna (fuzzy search). Saat ini bersifat opsional dan disiapkan *slot*-nya di arsitektur.

### Penegasan Konsep
*   **AI TIDAK Mengingat:** Model AI (LLM) tetap *stateless*.
*   **Injection Process:** Saat Agent butuh info, ia memanggil `memory.search`. Hasilnya (teks/JSON) disisipkan ke dalam prompt sistem sebagai "Context".
*   **Active Storage:** Saat Agent belajar hal baru, ia memanggil `memory.write` untuk menyimpannya ke database server.

---

## 4. Memory Classification

Setiap unit memori yang disimpan harus memiliki klasifikasi (Type) untuk menentukan aturan validasi dan penggunaannya.

### a. Business Rule (`business_rule`)
*   **Tujuan:** Menetapkan batasan keras logika bisnis yang harus dipatuhi AI.
*   **Contoh Isi:** "Pembelian di atas IDR 10 Juta wajib ada 3 pembanding vendor."
*   **Scope:** Organization (berlaku untuk seluruh perusahaan).
*   **Sifat:** **Locked** (Hanya bisa diubah oleh Human via Filament).

### b. System Constraint (`system_constraint`)
*   **Tujuan:** Batasan teknis sistem.
*   **Contoh Isi:** "Maksimal retry API ke ERP adalah 3 kali."
*   **Scope:** System (Global).
*   **Sifat:** **Locked** (Immutable oleh AI).

### c. Preference (`preference`)
*   **Tujuan:** Personalisasi interaksi untuk user tertentu.
*   **Contoh Isi:** "User A lebih suka laporan dalam bentuk tabel ringkas, bukan narasi panjang."
*   **Scope:** User (Spesifik per user ID).
*   **Sifat:** **Editable** (Bisa diupdate AI berdasarkan feedback user).

### d. Decision Log (`decision_log`)
*   **Tujuan:** Rekam jejak alasan pengambilan keputusan oleh AI (Audit Trail).
*   **Contoh Isi:** "Vendor X dipilih karena harga terendah dan stok tersedia (Ref: Quote #Q123)."
*   **Scope:** Organization / Transaction.
*   **Sifat:** **Append Only** (Tidak boleh diedit setelah ditulis).

### e. Documentation Reference (`documentation_reference`)
*   **Tujuan:** Pointer ke dokumen eksternal atau SOP.
*   **Contoh Isi:** "Prosedur Retur Barang mengacu pada Dokumen SOP-LOG-05 Bab 2."
*   **Scope:** Organization.
*   **Sifat:** **Editable** (Dapat diperbarui jika dokumen berubah).

---

## 5. MCP Communication Endpoint (Deskriptif)

Antarmuka ini mengikuti standar JSON-RPC 2.0 untuk MCP.

### a. `memory.write`
*   **Fungsi:** Menyimpan unit memori baru atau memperbarui yang statusnya *editable*.
*   **Input:** `content` (text), `memory_type` (enum), `tags` (array), `scope_id`.
*   **Output:** `memory_id`, `status` (success/failed).
*   **Validasi:** Cek apakah `memory_type` diizinkan untuk ditulis oleh AI. Cek duplikasi konten serupa (opsional).

### b. `memory.search`
*   **Fungsi:** Mengambil memori yang relevan berdasarkan query.
*   **Input:** `query` (text keywords), `memory_type` (filter), `limit` (int).
*   **Output:** List of memories `{ content, created_at, reliability_score }`.
*   **Batasan:** Hasil dibatasi jumlahnya agar tidak membebani context window.

### c. `memory.read`
*   **Fungsi:** Mengambil satu memori spesifik secara detail.
*   **Input:** `memory_id`.
*   **Output:** Full object memory termasuk metadata (siapa pembuatnya, kapan terakhir diubah).

### d. `memory.delete` (Restricted)
*   **Fungsi:** Menandai memori sebagai *deprecated* (Soft Delete).
*   **Input:** `memory_id`, `reason`.
*   **Validasi:** AI tidak boleh menghapus `business_rule` atau `system_constraint`.

### e. `memory.lock`
*   **Fungsi:** Mengunci memori agar tidak bisa diubah lagi (Immutable).
*   **Input:** `memory_id`.
*   **Output:** Status konfirmasi.

---

## 6. Filament Dashboard Role

Filament bertindak sebagai **Control Plane**.

1.  **Memory Viewer:**
    *   Tabel interaktif dengan filter berdasarkan `Type`, `User`, dan `Date`.
    *   Fitur pencarian global untuk konten memori.
2.  **Editor & Validator:**
    *   Formulir untuk Admin mengubah isi memori yang salah (misal: AI salah menangkap preferensi).
    *   Flagging manual: Admin bisa menandai memori sebagai "Verified" (tingkat kepercayaan tinggi).
3.  **Locking Mechanism:**
    *   Tombol "Lock" pada detail memori untuk mencegah AI menimpa data tersebut di masa depan.
4.  **Audit Trail UI:**
    *   Menggunakan fitur *Revisions* untuk melihat siapa yang mengubah memori (AI Agent atau Human Admin) dan kapan perubahannya (Diff view).
5.  **History:**
    *   Timeline view untuk melihat evolusi memori dari waktu ke waktu.

---

## 7. Memory Lifecycle

1.  **Creation (Kelahiran):**
    *   Oleh **AI**: Saat percakapan menghasilkan fakta baru (status: *Unverified*).
    *   Oleh **Human**: Input manual aturan bisnis via Filament (status: *Verified*).
2.  **Utilization (Penggunaan):**
    *   Memory dipanggil (`search`) dan disuntikkan ke prompt context.
3.  **Verification (Validasi):**
    *   Human Admin mereview memori baru di dashboard. Jika valid, status diubah menjadi *Verified*.
4.  **Update (Mutasi):**
    *   Jika tipe *Editable*, AI bisa memperbarui (misal: preferensi user berubah).
    *   Versi lama disimpan dalam history log.
5.  **Archival/Deletion (Kematian):**
    *   Memori yang usang di-*soft delete*.
    *   Memori tidak pernah dihapus fisik dari database demi audit, kecuali ada permintaan *hard delete* (GDPR/Compliance).

---

## 8. Rules Anti-Hallucination

Untuk mencegah halusinasi AI yang bersumber dari memori palsu:

1.  **Rule of Authority:**
    *   Memori bertipe `business_rule` dan `system_constraint` **HANYA** boleh ditulis/diedit oleh Human (via Filament). AI hanya punya akses *Read-Only* untuk tipe ini.
2.  **Conflict Resolution:**
    *   Jika ada informasi bertentangan antara memori AI dan memori Human, sistem memprioritaskan memori dengan status **Verified** (buatan Human).
3.  **Source Attribution:**
    *   Setiap potongan memori yang di-retrieve harus menyertakan metadata sumber (e.g., "Source: User Manual v1.2") agar AI bisa mengutip sumbernya, bukan mengarang.
4.  **No Implicit Update:**
    *   AI tidak boleh mengubah memori secara implisit (diam-diam). Setiap perubahan harus melalui pemanggilan fungsi `memory.write` yang tercatat log-nya.

---

## 9. Security & Scope

### Multi-Organization Support
*   Sistem didesain *multi-tenant* (secara logikal) menggunakan `team_id` atau `organization_id` pada setiap record memori.
*   **Isolation:** AI Agent milik Org A tidak akan pernah bisa mengakses memori milik Org B (filter level database/Eloquent Scope).

### Audit Logging
*   Setiap akses `read` dan `write` dicatat.
*   Penting untuk forensik jika AI melakukan kesalahan fatal dalam proses Procurement (misal: salah order barang).

### No Auto-Learn (Safety First)
*   Sistem **TIDAK** melakukan training/fine-tuning model secara otomatis.
*   Pembelajaran hanya terjadi pada level konteks (RAG). Ini mencegah "keracunan data" (*data poisoning*) yang sulit diperbaiki pada model yang sudah di-training.

---

## 10. Out of Scope (Fase Ini)

Hal-hal berikut **TIDAK** termasuk dalam lingkup desain saat ini:

1.  **Vector Database Implementation:** Pencarian masih menggunakan *Full-Text Search* (SQL Like/Match) database standar, belum menggunakan Pinecone/Milvus/pgvector.
2.  **File Storage / OCR:** Sistem tidak menyimpan file fisik (PDF/Gambar) atau memproses isinya. Hanya menyimpan teks referensi.
3.  **Real-time Voice Processing:** Fokus hanya pada teks.
4.  **AI Agent Logic:** Logika pengambilan keputusan AI ada di sisi Client/Agent, bukan di Memory Server ini.

---

# AI Agent Memory Contract — MCP Memory Module
**Peran:** AI Governance Architect  
**Status:** MANDATORY (Kontrak Perilaku Agent)  
**Tujuan:** Mencegah halusinasi, abuse memori, dan kebocoran konteks oleh AI Agent.

---

## 1. Agent Identity Definition

Setiap AI Agent yang berinteraksi dengan Memory Server wajib memiliki identitas yang terverifikasi:

- **`agent_id`:** Identifier unik untuk instansi agen tersebut.
- **`agent_type`:** Klasifikasi peran agen (misal: `coder`, `architect`, `reviewer`).
- **`repository_context`:** Metadata proyek tempat agen sedang bekerja.
- **`organization_context`:** Konteks organisasi pemilik proyek.

---

## 2. Mandatory Context Rule

Setiap request ke MCP Memory Server **WAJIB** menyertakan informasi berikut. Request yang tidak lengkap akan ditolak (Reject-by-Default):

1. **`repository_id`:** UUID repository aktif yang sedang ditangani.
2. **`intent`:** Deklarasi tujuan aksi (`read` untuk pengambilan konteks, `write` untuk penyimpanan informasi).
3. **`memory_type`:** Tipe memori spesifik yang menjadi target operasi.

---

## 3. Read Permission Rules (Scope Boundary)

Agent hanya diizinkan membaca memori yang memenuhi kriteria isolasi berikut:

- **Urutan Prioritas:** Agent wajib memproses memori dari `Repository Scope` terlebih dahulu, diikuti oleh `Organization Scope`, dan terakhir `System Scope`.
- **Exclusion:** Agent **DILARANG** mengakses memori milik `repository_id` lain di luar konteks yang sedang aktif.
- **Filtering:** Memori yang bersifat administratif atau berisi rahasia sistem (secrets) tidak akan pernah dikirimkan ke agen.

---

## 4. Write Permission Rules

### A. Memori yang BOLEH ditulis AI:
- **`decision_log`:** Dokumentasi keputusan teknis atau logika yang diambil selama sesi.
- **`temporary_note`:** Catatan sementara untuk kebutuhan koordinasi antar langkah.
- **`execution_summary`:** Ringkasan hasil eksekusi tugas.

### B. Memori yang DILARANG ditulis AI:
- **`business_rule`:** Aturan bisnis fundamental organisasi.
- **`system_constraint`:** Batasan arsitektur platform.
- **Locked Memory:** Segala bentuk memori yang sudah berstatus `locked`.
- **Repository Governance:** Aturan mengenai bagaimana sebuah repo harus dikelola.

---

## 5. Memory Write Preconditions (Epistemological Guardrails)

AI Agent diizinkan menulis memori hanya jika informasi tersebut memenuhi syarat berikut:

1. **Berdasarkan Aksi Nyata:** Hasil dari eksekusi perintah, debug, atau verifikasi.
2. **Bukan Asumsi:** Dilarang menyimpan spekulasi tentang cara kerja sistem.
3. **Bukan Prediksi:** Dilarang menyimpan dugaan masa depan sebagai fakta.
4. **Bukan Opini:** Hanya data faktual dan keputusan final yang disimpan.

---

## 6. Memory Promotion & Control

- **No Auto-Promotion:** Agent dilarang mencoba memindahkan memori dari level `Repository` ke `Organization`.
- **Status Control:** Hanya manusia (Admin) yang boleh mengubah status `unverified` menjadi `verified`.
- **No Locking:** Agent tidak memiliki izin teknis maupun otoritas untuk mengunci (`lock`) memori.

---

## 7. Conflict Awareness Rule

Sebelum memberikan jawaban atau melakukan aksi kritikal, Agent wajib:

1. Melakukan `memory-search` untuk memastikan tidak ada aturan yang dilanggar.
2. Menghormati `locked memory` sebagai prioritas tertinggi yang tidak boleh dibantah.
3. Memberitahu pengguna jika ditemukan konflik antara instruksi user dan memori sistem.

---

## 8. Memory Usage Disclosure

Agent harus memperlakukan memori sebagai:
- **Referensi Konteks:** Memori adalah panduan tambahan, bukan pengganti logika dasar model.
- **Scope-Limited:** Informasi dalam memori hanya valid dalam scope yang ditentukan (misal: hanya untuk repo ini).
- **Non-Absolute:** Memori berkategori `unverified` harus dianggap sebagai informasi sekunder.

---

## 9. Forbidden Behaviors (Daftar Terlarang)

- **Rule Synthesis:** Menyimpulkan aturan bisnis baru secara implisit tanpa konfirmasi.
- **Context Leakage:** Menggunakan memori dari Proyek A untuk mengerjakan Proyek B.
- **Interpretation Storage:** Menyimpan interpretasi subjektif agen terhadap sebuah instruksi.
- **Conclusion Writing:** Menulis kesimpulan bisnis yang seharusnya menjadi domain keputusan manusia.

---

## 10. Violation Handling & Fallback

Jika sebuah request ditolak oleh Memory Server karena pelanggaran kontrak:

- **Internal Error Handling:** Agent harus mencatat alasan penolakan dalam log internal.
- **Fallback Behavior:** Agent harus melanjutkan tugas tanpa asumsi memori yang ditolak, namun wajib memberitahu user tentang keterbatasan konteks tersebut.
- **User Notification:** Agent harus menyampaikan kepada user dengan sopan jika ada aksi yang dibatasi oleh kebijakan governance (misal: "Saya tidak dapat mengubah Business Rule ini karena statusnya Terkunci").

---

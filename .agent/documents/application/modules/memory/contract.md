# Kontrak Implementasi Final — MCP Memory Module
**Peran:** Principal Architect  
**Status:** MANDATORY (Mengikat)  
**Tujuan:** Meniadakan interpretasi bebas, memastikan isolasi repository, dan menjaga integritas memori.

---

## 1. Fixed Terminology

Istilah-istilah berikut bersifat baku dan tidak boleh digantikan oleh sinonim lain dalam kode maupun dokumentasi teknis:

- **Memory:** Unit informasi terkecil yang disimpan (fakta/log).
- **Scope:** Batasan visibilitas dan kepemilikan memori (System, Organization, User, Repository).
- **Repository:** Proyek kode spesifik yang menjadi boundary utama data.
- **Locked Memory:** Memori dengan status immutable yang tidak dapat dimodifikasi oleh AI.
- **Structured Memory:** Memori yang memiliki skema tipe data dan metadata yang didefinisikan secara eksplisit.
- **Semantic Memory:** Kemampuan sistem untuk mencari memori berdasarkan makna (kueri tekstual), bukan sekadar exact match.

---

## 2. Non-Negotiable Rules (Aturan Mutlak)

1. **AI Restriction:** AI Agent **DILARANG** membuat atau memodifikasi memori tipe `business_rule` dan `system_constraint`.
2. **Context Requirement:** Setiap request ke MCP Endpoint **WAJIB** menyertakan `repository_id` yang valid.
3. **Immutability:** Memori dengan status `Locked` tidak boleh diubah oleh aktor mana pun (termasuk Admin) tanpa melalui flow `Unlock` formal yang tercatat di audit log.
4. **No Override:** Aturan pada `System Scope` tidak boleh ditimpa oleh `Repository Scope` jika aturan tersebut ditandai sebagai `Enforced`.

---

## 3. Required Context Contract

Sistem **WAJIB** menolak request jika konteks berikut tidak terpenuhi secara lengkap:

- **Repository Identifier:** UUID/Slug proyek aktif.
- **Organization Identifier:** UUID organisasi pemilik repo.
- **Agent Identifier:** ID unik agen yang melakukan request.
- **Intent:** Deklarasi aksi (`Read` atau `Write`).

**Behavior:** Kegagalan salah satu unsur konteks menghasilkan respon `400 Bad Request` dengan error detail "Missing Contextual Requirements".

---

## 4. Memory Write Contract

- **Aktor:** AI Agent dan Human Admin.
- **Izin Tulis AI:** Hanya diizinkan menulis tipe `decision_log`, `preference`, dan `documentation_ref` pada `Repository Scope`.
- **Kondisi Valid:** Teks tidak kosong, konteks repo valid, dan tidak melanggar batasan `Locked`.
- **Kondisi Penolakan:** Percobaan menulis ke memori yang sudah `Locked` atau mencoba menembus scope organisasi dari level agen.

---

## 5. Memory Read Contract

- **Urutan Resolusi (Priority Order):**
  1. Repository Scope (Tertinggi)
  2. User Scope
  3. Organization Scope
  4. System Scope (Terendah)
- **Batas Maksimum:** Sistem hanya mengembalikan maksimal **10 unit memori** per search request untuk efisiensi context window LLM.
- **Format Injection:** Data dikirim sebagai array of objects yang mencakup `content`, `type`, dan `scope_origin`.

---

## 6. Conflict Resolution Contract

Sistem menyelesaikan konflik data berdasarkan hierarki berikut:

- **System vs Organization:** System menang jika bersifat `Enforced`.
- **Organization vs Repository:** Repository menang untuk detail implementasi teknis.
- **Locked vs Editable:** `Locked` selalu menang.
- **Human vs AI Generated:** Data `Verified` (Human) secara otomatis menimpa data `Unverified` (AI) dalam hasil pencarian jika terdapat kontradiksi.

---

## 7. Audit Contract

1. **Mandatory Logging:** Setiap operasi penulisan (`Write`, `Update`, `Delete`) wajib menghasilkan satu entry di `MemoryAuditLog`.
2. **Versioning:** Update pada konten memori tidak menimpa data lama, melainkan menciptakan baris baru atau snapshot versi lama.
3. **No Hard Delete:** Data yang dihapus hanya mengalami perubahan status menjadi `Archived`.
4. **Traceability:** Setiap log audit wajib mencantumkan `request_id` untuk kebutuhan debugging lintas sistem.

---

## 8. Governance & Approval Flow

- **Editable:** Semua memori berstatus `unverified` dapat diedit oleh Admin.
- **Locking:** Hanya Admin dengan role `Architect` atau `Manager` yang diizinkan melakukan locking.
- **Approval:** Memori yang dibuat oleh AI (`unverified`) harus diubah menjadi `verified` oleh manusia sebelum dianggap sebagai pedoman resmi proyek.

---

## 9. Failure Behavior

- **Memory Not Found:** Mengembalikan empty array, **bukan** error. Agen harus mampu bekerja tanpa memori opsional.
- **Wrong Context:** Memberikan respon error `403 Forbidden`.
- **Illegal Write Attempt:** Menolak aksi dan mencatat upaya pelanggaran tersebut ke Security Log.
- **Unresolved Conflict:** Jika dua aturan `Locked` bertentangan, sistem mengembalikan error sistem yang mewajibkan intervensi manusia (Architect).

---

## 10. Definition of Done (DoD)

Fitur dianggap selesai jika dan hanya jika:

- [ ] Seluruh aturan isolasi repository terverifikasi melalui Automated Tests.
- [ ] Audit log mencatat setiap perubahan tanpa celah (Zero-Gap Audit).
- [ ] Perilaku 'Scope Resolution' bekerja sesuai matriks prioritas yang ditetapkan.
- [ ] Tidak ada fitur "tersembunyi" atau perilaku implisit di luar kontrak ini.

---

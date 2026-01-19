# Filament Dashboard UX & Governance Blueprint — MCP Memory Module
**Peran:** Product & Governance Architect  
**Status:** FINAL (Panduan Implementasi UI/UX & Otoritas)  
**Tujuan:** Memberikan antarmuka bagi manusia untuk mengawasi, memvalidasi, dan mengendalikan long-term memory yang dihasilkan oleh AI secara aman dan terstruktur.

---

## 1. User Role Definition

Penerapan hak akses dilakukan secara granular berbasis scope dan tanggung jawab:

- **Super Admin:** Akses penuh ke seluruh sistem, termasuk `System Scope` dan manajemen organisasi.
- **Organization Admin:** Kontrol penuh atas seluruh memori dalam satu organisasi, termasuk manajemen repository.
- **Repository Maintainer:** Hak kelola memori pada repository yang ditugaskan (Write/Edit/Lock pada Repo Scope).
- **Viewer:** Hak baca saja (Audit Only) untuk kebutuhan observabilitas dan debugging.

---

## 2. Permission Matrix

| Aksi                   | Super Admin | Org Admin | Repo Maintainer | Viewer |
| :--------------------- | :---------: | :-------: | :-------------: | :----: |
| Read (All Scopes)      |     Ya      |    Ya     |       Ya        |   Ya   |
| Create Memory (Repo)   |     Ya      |    Ya     |       Ya        | Tidak  |
| Create Business Rule   |     Ya      |    Ya     |    Terbatas*    | Tidak  |
| Edit Unverified Memory |     Ya      |    Ya     |       Ya        | Tidak  |
| Lock Memory            |     Ya      |    Ya     |       Ya        | Tidak  |
| Deprecate Memory       |     Ya      |    Ya     |       Ya        | Tidak  |
| View Audit Logs        |     Ya      |    Ya     |    Terbatas*    |   Ya   |

*\* Terbatas pada Repository context yang ditugaskan.*

---

## 3. Memory Management UX Flow

### A. Alur Validasi Memori (Human-in-the-loop)
1. **Discovery:** Admin melihat list memori dengan badge `Unverified` (Hasil input AI).
2. **Review:** Admin meninjau konten dan klasifikasi.
3. **Action:** Admin memilih aksi `Verify` (status berubah menjadi verified) atau `Edit` jika terdapat kesalahan pemahaman.
4. **Promotion:** Admin dapat melakukan `Lock` untuk memori yang menjadi aturan fundamental.

### B. Alur Archival & Deprecation
- Memori yang sudah tidak relevan tidak dihapus, melainkan di-set menjadi `Deprecated`.
- UI harus menanyakan `Reason for Deprecation` untuk kebutuhan audit di masa depan.

---

## 4. Repository Context Selector

Isolasi data di UI ditegakkan melalui pola berikut:
- **Global Selector:** Dropdown repository wajib ada di top-bar atau sidebar untuk menyeleksi konteks aktif.
- **Contextual View:** Saat repository dipilih, hanya memori milik repo tersebut (+ Org & System rules) yang muncul.
- **Scope Badge:** Setiap baris memori wajib memiliki label warna yang jelas:
  - `Purple`: System Scope (Read-only for others).
  - `Blue`: Organization Scope.
  - `Green`: Repository Scope.

---

## 5. Memory Type Governance

Aturan visual dan perilaku berdasarkan tipe data:
- **Business Rule:** Di-highlight dengan border khusus. Hanya dapat dibuat/diedit oleh level Admin ke atas.
- **Decision Log:** Ditampilkan dalam format timeline (List View). Biasanya bersifat Read-only setelah disimpan untuk menjaga integritas sejarah proyek.
- **Documentation Ref:** Bersifat editable oleh Maintainer untuk memperbaiki referensi dokumen yang salah.

---

## 6. Locking UX Pattern

Karena penguncian bersifat kritis:
- **Confirmation Modal:** Wajib menampilkan pesan peringatan: *"Memori yang dikunci tidak dapat diubah oleh AI Agent selamanya."*
- **Re-authentication:** (Opsional) Meminta password atau konfirmasi ulang.
- **Locked State UI:** Tombol `Edit` dan `Delete` otomatis disembunyikan/dinonaktifkan pada record yang terkunci.

---

## 7. Version History Viewer

Menyediakan transparansi perubahan:
- **Side-by-side Diff:** Menampilkan perbandingan `Old Value` dan `New Value`.
- **Identity:** Menampilkan foto/nama aktor (Human Admin nama/ID, AI Agent dengan logo bot).
- **Versioning Timeline:** Breadcrumb atau list vertikal untuk berpindah antar versi.

---

## 8. Safety & Compliance UX Elements

- **AI-Generated Badge:** Semua memori yang masuk via MCP `memory.write` wajib memiliki badge "Input by AI" untuk membedakannya dari input manual manusia.
- **Warning Banner:** Tampil di bagian atas form edit jika memori tersebut sedang digunakan dalam referensi aktif di scope lain.
- **Irreversible Indicator:** Ikon gembok merah pada memori berstatus `locked`.

---

## 9. Forbidden UX Behaviors

1. **No Bulk Auto-Approve:** Dilarang menyediakan tombol "Approve All" pada memori yang belum diverifikasi secara manual.
2. **No Silent Update:** Setiap perubahan oleh admin wajib memicu kolom `Reason for Change` jika versioning aktif.
3. **No Cross-Repo Bulk Move:** Memindah memori antar repository secara massal tanpa pengecekan manual.

---

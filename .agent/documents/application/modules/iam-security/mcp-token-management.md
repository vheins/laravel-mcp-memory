# Desain Fitur: MCP Token Management

Dokumentasi ini menjelaskan desain teknis dan UX untuk fitur manajemen token MCP (Model Context Protocol) pada aplikasi. Fitur ini memungkinkan pengguna (human) untuk membuat token akses jangka panjang yang aman untuk digunakan oleh AI Agent atau tools eksternal.

---

## 1. Profile Menu Placement

Fitur ini akan ditempatkan pada **Halaman Profile User** di Filament Panel.

- **Lokasi**: Halaman Edit Profile (biasanya diakses via dropdown user di pojok kanan atas > Profile).
- **Asesibilitas**: Token dikelola secara personal oleh masing-masing user. User hanya bisa melihat dan mengelola token miliknya sendiri.
- **Bukan Admin Global**: Fitur ini **tidak** ditempatkan di menu sidebar utama atau menu admin global, untuk menjaga privasi dan konteks kepemilikan token.

**Catatan Implementasi**:
- Pastikan method `->profile()` aktif pada `DashboardPanelProvider` atau panel provider terkait.
- Jika menggunakan custom profile page, tambahkan komponen manajemen token di dalamnya sebagai section baru (misalnya tab atau fieldset "MCP Access Tokens").

## 2. Token Purpose Definition

Token ini memiliki tujuan spesifik sebagai kredensial otentikasi untuk komunikasi antara **AI Agent / Eksternal Tool** dengan **MCP Memory Server** aplikasi ini.

- **Penggunaan Utama**:
  - Otentikasi request ke MCP Server endpoints.
  - Identitas agen saat melakukan operasi baca/tulis memori.
- **Format Header**:
  Client harus mengirimkan token via HTTP Authorization Header:
  ```http
  Authorization: Bearer mcp_v1_...
  ```
- **Konteks**: Token ini mewakili user pemiliknya. Aksi yang dilakukan oleh agent menggunakan token ini akan tercatat sebagai aksi yang dilakukan atas nama user tersebut.

## 3. Token Type

Sistem token menggunakan infrastruktur **Laravel Sanctum**.

- **Driver**: Laravel Sanctum Personal Access Token.
- **Expiration**: **Tanpa Kedaluwarsa** (`expires_at = null`).
  - *Alasan*: AI Agent (seperti VS Code Extension atau Cursor) membutuhkan koneksi persisten tanpa perlu login ulang berkala yang mengganggu workflow coding. Keamanan dijaga melalui mekanisme revocation manual.

## 4. Token Abilities (Scope)

Token harus memiliki *abilities* (scope) yang spesifik untuk membatasi akses agent. Tidak boleh memberikan akses "wildcard" (`*`) secara default.

**Abilities Minimal**:
1.  `mcp:read`: Izin untuk membaca memori dan konteks.
2.  `mcp:write`: Izin untuk menyimpan memori baru.
3.  `mcp:admin`: **(HUMAN ONLY)** Izin untuk manajemen konfigurasi sensitif. **Jangan** berikan ini ke AI Agent standar.

**Rekomendasi Agent**:
- Untuk coding assistant (Cursor/Windsurf): Berikan `mcp:read` dan `mcp:write`.

## 5. Token Creation Flow (UX)

Alur pembuatan token dirancang untuk keamanan maksimal:

1.  **Akses Profile**: User membuka halaman Profile.
2.  **Trigger**: User klik tombol **"Generate MCP Token"**.
3.  **Form Modal**:
    - **Nama Token**: Input text (Wajib). Placeholder: "Laptop VS Code", "Cursor Agent".
    - **Abilities**: Checkbox/Select (Wajib). Default terpilih: `mcp:read`, `mcp:write`.
4.  **Konfirmasi**: User menekan "Create".
5.  **Tampilan Token**:
    - Sistem menampilkan token **HANYA SEKALI** dalam modal sukses.
    - Copy button tersedia.
    - Warning text: *"Simpan token ini sekarang. Anda tidak akan bisa melihatnya lagi."*

## 6. Token Display Rules

- **Plaintext Display**: Token lengkap hanya ditampilkan saat **detik pertama setelah pembuatan** (creation response).
- **Listing**: Pada daftar token di halaman profile, hanya tampilkan:
  - **Nama Token**
  - **Last Used At** (kapan terakhir dipakai)
  - **Created At**
  - **Abilities** (badge)
- **Masking**: Jangan pernah menampilkan token yang sudah tersimpan di database ke UI, bahkan sebagian (misal `abcd***`) sebaiknya dihindari jika tidak perlu, atau cukup 4 karakter terakhir untuk identifikasi.

## 7. Token Revocation Flow

Mekanisme pembatalan akses (Revoke/Delete) harus mudah diakses:

- **Aksi**: Tombol "Revoke" atau icon "Delete" (Trash) di sebelah setiap item token pada list.
- **Konfirmasi**: Tampilkan dialog konfirmasi *"Apakah Anda yakin ingin menghapus akses ini? Agent yang menggunakan token ini akan langsung kehilangan akses."*
- **Efek**: Saat dikonfirmasi, record token di database langsung dihapus. Request berikutnya dari agent dengan token tersebut akan mendapat respon `401 Unauthorized`.

## 8. Token Naming Convention

Mewajibkan user memberikan nama deskriptif untuk audit trail yang baik.

**Format Disarankan (untuk user)**:
- `mcp-{client}-{environment}`
- Contoh:
    - `mcp-vscode-laptop`
    - `mcp-claude-web`
    - `mcp-ci-github-actions`
    - `mcp-prod-agent`

Penamaan ini memudahkan user mengidentifikasi token mana yang bocor atau perlu direvoke jika perangkat hilang.

## 9. Audit & Logging

Setiap interaksi token dan manajemen token dicatat:

- **Pembuatan Token**: Log siapa (user ID), kapan, IP address.
- **Penggunaan Token**:
    - Manfaatkan fitur `last_used_at` bawaan Sanctum untuk tracking aktivitas terakhir.
    - Pada level MCP Server, log setiap request yang masuk (Middleware logging) mencatat Token ID (bukan value tokennya) atau Nama Token.

## 10. Security Rules

Aturan keamanan fundamental yang **WAJIB** dipatuhi:

1.  **No Plaintext Storage**: Token disimpan dalam bentuk *hashed* di database (bawaan Sanctum).
2.  **No Redisplay**: API endpoint untuk list token **tidak boleh** mengembalikan value token, hanya metadata.
3.  **Immutable**: Token yang sudah dibuat tidak bisa diedit (scope/nama). Jika salah, user harus revoke dan buat baru. (Simplifikasi keamanan).
4.  **Revoke & Regenerate**: Jika user merasa token tidak aman, instruksikan untuk revoke segera.

## 11. Filament UI Implication

Komponen UI yang perlu dibangun di Filament:

- **Repeater / Table Layout** di Profile Page:
    - Token List sebagai tabel sederhana atau list group.
- **Action Button**: `GenerateTokenAction` (Modal Form).
- **Abilities Selection**: Checkbox `mcp:read`, `mcp:write`.
- **Copy to Clipboard Component**: Di modal sukses pembuatan token.
- **Empty State**: Pesan informatif jika belum ada token ("Belum ada token MCP aktif").
- **Warning Banner**: (Optional) Banner kecil di atas list mengingatkan bahwa token bersifat rahasia setara password.

## 12. Out of Scope

Hal-hal berikut **TIDAK** termasuk dalam lingkup desain ini:

- **Token Auto-rotation**: Tidak ada mekanisme ganti token otomatis.
- **Auto Expiration**: Token berlaku selamanya sampai direvoke manual.
- **Refresh Token Mechanism**: Tidak diperlukan karena token bersifat long-lived.

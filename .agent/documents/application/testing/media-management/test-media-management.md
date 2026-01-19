# DOKUMENTASI PENGUJIAN: Media Management

> Dokumen ini berisi rencana dan skenario pengujian untuk fitur Media Management.

---

## Header & Navigasi

- [Kembali ke Ikhtisar Modul](../../../modules/media-management/overview.md)
- [Link ke Spesifikasi API](../../../api/media-management/api-media-management.md)

---

## 1. Ikhtisar Pengujian (Test Overview)

- **Nama Modul:** Media Management
- **Lingkup Pengujian:** File Upload, validation, Storage Integration (Mock), Polymorphic Attachment.
- **Alat Pengujian:** Supertest, Mock Storage (Local), Multer Test.

---

## 2. Strategi Pengujian

### 2.1 Pengujian Unit
- **Komponen Kritis:** File Validator (MIME/Size), Filename Generator (Uniqueness).

### 2.2 Pengujian Integrasi
- **Endpoint API:** `POST /upload`, `POST /attach`.
- **Database:** Pastikan record `media` dan `media_attachments` tersimpan dengan benar.

### 2.3 Monkey Testing
- **Tujuan:** Menguji upload file korup atau malformed multipart request.

---

## 3. Test Scenarios (Backend / API)

### 3.1 Positive Cases (Happy Paths)

| ID          | Test Case               | Pre-condition       | Input Data               | Expected Result           | Priority |
| :---------- | :---------------------- | :------------------ | :----------------------- | :------------------------ | :------- |
| MED-POS-001 | **Upload Gambar Valid** | User Login          | File: `img.jpg` (1MB)    | 201 Created, URL returned | High     |
| MED-POS-002 | **Upload Dokumen PDF**  | User Login          | File: `doc.pdf` (2MB)    | 201 Created, URL returned | High     |
| MED-POS-003 | **Attach ke Product**   | Media & Product ada | Map MediaID -> ProductID | 200 OK, Relation Created  | High     |

### 3.2 Negative Cases (Validation Rules)

| ID          | Test Case                     | Pre-condition    | Input Data               | Expected Result          | Priority |
| :---------- | :---------------------------- | :--------------- | :----------------------- | :----------------------- | :------- |
| MED-NEG-001 | **Upload File Terlalu Besar** | Max size 5MB     | File: `video.mp4` (50MB) | 413 Payload Too Large    | High     |
| MED-NEG-002 | **Upload Tipe Terlarang**     | Allowed: Img/PDF | File: `virus.exe`        | 422 Unprocessable Entity | High     |
| MED-NEG-003 | **Attach ke Entitas Gaib**    | -                | EntityID: `uuid-random`  | 404 Not Found            | Medium   |

### 3.3 Monkey Tests (Chaos & Stability)

| ID          | Test Case                 | Approach                          | Input Data      | Expected Result                | Priority |
| :---------- | :------------------------ | :-------------------------------- | :-------------- | :----------------------------- | :------- |
| MED-MNK-001 | **Partial Upload**        | Putuskan koneksi saat upload 50%  | Stream terputus | Server clean up temp file      | Low      |
| MED-MNK-002 | **Bomba File (Zip Bomb)** | Upload file kompresi rasio tinggi | Zip Bomb        | Anti-virus / Size limit reject | Critical |

---

## 4. Pengujian Keamanan (Security Testing)

- **Malware Scan:** (Idealnya) setiap file di-scan anti-virus.
- **Path Traversal:** Pastikan nama file disanitasi agar tidak bisa menimpa file sistem (`../../etc/passwd`).

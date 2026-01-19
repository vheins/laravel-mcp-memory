# DOKUMENTASI PENGUJIAN: Modul Taxonomy
 
 > Dokumen ini berisi rencana dan skenario pengujian untuk fitur Taxonomy.
 
 ---
 
 ## Header & Navigasi
 
 - [Kembali ke Ikhtisar Modul](../../modules/taxonomy/overview.md)
 - [Link ke Spesifikasi API](../../api/taxonomy/api-taxonomy.md)
 
 ---
 
 ## 1. Ikhtisar Pengujian (Test Overview)
 
 - **Nama Modul:** Modul Taxonomy
 - **Lingkup Pengujian:** CRUD Taxonomy, Hierarki Term, Pelampiran Polimorfik, Validasi.
 - **Alat Pengujian:** *[Sebutkan tools, misal: Unit Testing Framework, HTTP Assertion Library]*
 
 ---
 
 ## 2. Strategi Pengujian
 
 ### 2.1 Pengujian Unit (Unit Testing)
 - **Komponen Kritis:** Logika Hierarki (mencegah ketergantungan melingkar), Cek keunikan Kode.
 
 ### 2.2 Pengujian Integrasi (Integration Testing)
 - **Endpoint API:**
   - `GET /taxonomies`
   - `POST /attachments`
 - **Interaksi Database:**
   - Verifikasi populasi tabel EntityTerm.
 
 ### 2.3 Monkey Testing
 - **Tujuan:** Menguji hierarki melingkar dan performa pohon data yang besar.
 
 ---
 
 ## 3. Test Scenarios (Backend / API)
 
 ### 3.1 Positive Cases (Happy Paths)
 
 | ID          | Test Case                   | Pre-condition      | Input Data                         | Expected Result      | Priority |
 | :---------- | :-------------------------- | :----------------- | :--------------------------------- | :------------------- | :------- |
 | TAX-POS-001 | **Buat Taxonomy**           | Token Admin        | Code: `skill`, Name: `Skill`       | 201 Created          | High     |
 | TAX-POS-002 | **Buat Term**               | Taxonomy ada       | Code: `backend`, Taxonomy: `skill` | 201 Created          | High     |
 | TAX-POS-003 | **Lampirkan Term**          | Entity & Term ada  | Entity: `User-1`, Term: `backend`  | 201 Created / 200 OK | High     |
 | TAX-POS-004 | **Ambil Terms (Hierarkis)** | Struktur pohon ada | GET /terms?hierarchy=true          | 200 OK, Nested JSON  | Medium   |
 
 ### 3.2 Negative Cases (Validation Rules)
 
 | ID          | Test Case                  | Pre-condition     | Input Data             | Expected Result       | Priority |
 | :---------- | :------------------------- | :---------------- | :--------------------- | :-------------------- | :------- |
 | TAX-NEG-001 | **Duplikat Kode Taxonomy** | Code `skill` ada  | Buat `skill`           | 409 Conflict          | High     |
 | TAX-NEG-002 | **Circular Parent**        | A adalah parent B | Set B sebagai parent A | 400 Bad Request / 422 | Medium   |
 | TAX-NEG-003 | **Lampirkan Term Invalid** | -                 | Term: `unknown_code`   | 404 Not Found         | High     |
 
 ### 3.3 Monkey Tests (Chaos & Stability)
 
 | ID          | Test Case              | Approach                          | Input Data   | Expected Result                 | Priority |
 | :---------- | :--------------------- | :-------------------------------- | :----------- | :------------------------------ | :------- |
 | TAX-MNK-001 | **Deep Nesting**       | Buat Term dengan 100 level parent | -            | DB menangani atau API menolak   | Low      |
 | TAX-MNK-002 | **Massive Attachment** | Lampirkan 10k terms ke 1 entity   | Loop request | Cek Performa (Penggunaan Index) | Medium   |
 
 ---
 
 ## 4. Pengujian Keamanan (Security Testing)
 
 - **Otorisasi:** Hanya Admin yang bisa mendefinisikan Taxonomies.
 - **Kebocoran Data:** Pastikan metadata privat tidak terekspos.

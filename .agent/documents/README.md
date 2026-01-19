# Aturan Struktur Folder Dokumentasi

File ini menjelaskan standar struktur folder untuk dokumentasi dalam proyek ini.

## Struktur Folder

Dokumentasi harus diorganisir secara terstruktur untuk memudahkan navigasi dan pemeliharaan.

### 1. Dokumentasi Modul
Setiap dokumentasi resource atau fitur dalam modul harus ditempatkan dalam direktori `.agent/documents/application/modules/` dan mengikuti format path berikut:

```
.agent/documents/application/modules/<nama-module>/(resource).md
```

Contoh:
- `.agent/documents/application/modules/auth/login.md`
- `.agent/documents/application/modules/payment/gateway.md`

### 2. Dokumentasi API
Dokumentasi spesifikasi API harus dipisahkan dari dokumentasi modul dan ditempatkan dalam direktori `.agent/documents/application/api/`.

```
.agent/documents/application/api/<nama-module>/api-<fitur>.md
```

Contoh:
- `.agent/documents/application/api/auth/api-login.md`
- `.agent/documents/application/api/payment/api-gateway.md`

### 3. Dokumentasi Testing
Dokumentasi pengujian (testing) ditempatkan secara terpisah dalam direktori `.agent/documents/application/testing/` dengan struktur per modul.

```
.agent/documents/application/testing/<nama-module>/testing.md
```
atau spesifik per resource:
```
.agent/documents/application/testing/<nama-module>/test-(resource).md
```

Pastikan semua dokumentasi teknis, API, dan pengujian tersentralisasi dengan rapi untuk memudahkan pemeliharaan dan navigasi.

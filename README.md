# Vibe Coding Template

Selamat datang di **Vibe Coding Template**. Repository ini bukan hanya template kode biasa, melainkan sebuah **Framework** yang dirancang khusus sebagai landasan kokoh untuk:

1.  **Technical Documentation**: Standarisasi dokumentasi fitur, API, dan pengujian.
2.  **Task Management**: Pengelolaan tugas yang terstruktur untuk tim pengembang.
3.  **Project Management**: Fondasi manajemen proyek berbasis teks yang efisien.

Framework ini bertujuan untuk menghilangkan ambiguitas dalam pengembangan perangkat lunak dengan menyediakan struktur dan alur kerja (workflow) yang jelas antara spesifikasi bisnis dan implementasi teknis.

## 🤖 Workflows (Agentic Automation)

Template ini dilengkapi dengan *Agentic Workflows* untuk mengotomatisasi tugas-tugas repetitif dalam penulisan dokumentasi dan manajemen tugas. Anda dapat memanggil workflow ini menggunakan perintah berikut:

### 1. Create Documentation (`/create-documentation`)
Workflow cerdas untuk membuat set dokumentasi awal (Overview, Feature, API, Testing) untuk modul baru.
- **Fungsi**: Melakukan riset konteks, merencanakan fitur/endpoint/scenario, dan membuat file dokumentasi lengkap berdasarkan template standar.
- **Trigger**: Gunakan saat memulai modul atau fitur besar baru.

### 2. Update Documentation (`/update-documentation`)
Workflow untuk menjaga sinkronisasi antara dokumen saat terjadi perubahan.
- **Fungsi**: Menjalankan prinsip *"Change One, Check All"* untuk memastikan tidak ada gap antara Logika Bisnis, API Spec, dan Test Scenario.
- **Trigger**: Gunakan setiap kali ada revisi fitur, perubahan endpoint API, atau penambahan skenario tes.

### 3. Update Task (`/update-task`)
Workflow untuk memperbarui status tugas implementasi.
- **Fungsi**: Melacak progres pengerjaan fitur secara granular.

---

## 📚 Struktur Dokumentasi

Dokumentasi teknis disusun di dalam direktori `.agent/documents/` agar root folder tetap bersih, namun tetap memberikan wawasan mendalam bagi AI agent maupun pengembang.

### Tautan Cepat

- **[📖 Beranda Dokumentasi](.agent/documents/README.md)**: Pusat dari segala dokumentasi teknis.
- **[📦 Modul Aplikasi](.agent/documents/application/modules/README.md)**: Spesifikasi fitur, Aturan Bisnis, dan ERD.
- **[🔌 Spesifikasi API](.agent/documents/application/api/README.md)**: Kontrak REST API dan detail endpoint.
- **[✅ Pengujian (Testing)](.agent/documents/application/testing/README.md)**: Skenario pengujian (Positif, Negatif, Monkey) dan strateginya.

## 🚀 Memulai

1.  **Jelajahi Modul**: Periksa `application/modules` untuk memahami logika bisnis domain.
2.  **Tinjau API**: Lihat `application/api` untuk titik integrasi.
3.  **Jalankan Test**: Ikuti strategi yang ada di `application/testing`.
4.  **Gunakan Workflow**: Manfaatkan workflow `.agent/workflows/` untuk mempercepat pekerjaan Anda.

## 📁 Struktur Proyek

```bash
.
├── .agent/
│   ├── documents/          # Hub Dokumentasi Terpusat
│   │   ├── application/    # Dokumen spesifik aplikasi (API, Modules, Testing)
│   │   ├── templates/      # Template standar untuk dokumen baru
│   │   └── README.md       # Indeks Dokumentasi Global
│   └── workflows/          # Agentic Workflows (Create/Update Docs)
├── README.md               # Anda di sini (Framework Overview)
└── [Source Code]           # Kode sumber aplikasi (akan diimplementasikan)
```
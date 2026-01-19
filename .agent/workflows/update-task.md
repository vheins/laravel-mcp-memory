---
description: Memperbarui status tugas dalam daftar tugas implementasi
---

# 1. Sync Logic
1. Buka Feature Doc modul di `.agent/documents/application/modules/`.
2. Sync status tabel "Implementation Tasks" di Feature Doc ke `.agent/tasks/implementation-tasks.md`.
3. Gunakan `replace_file_content` untuk mengubah checklist `[ ]` -> `[x]` atau status `Todo` -> `Done`.

# 2. Finalisasi
- **Verify**: `git status`.
// turbo
- **Commit**: `git add .agent/tasks/implementation-tasks.md && git commit -m "docs(tasks): sync task status from feature docs"`
